//=============================================================================
//
//-- Javascript for the Tag Container                                        --
//
// TODO : why do I use document.addEventListener for the section that looks for
// clicks on the tag X's.  Shouldn't it be more targeted to that element?
//=============================================================================

// this is JS reference to the tag-container we're working with
const tagContainer = document.querySelector('.linne-tag-container');

// create a new input
const tagInput = document.querySelector('.linne-tag-container input');
// create an alias for the datalist
const subjectDatalistInput = document.querySelector('#subject-input');

// this array is so that the backend or API calls have some place in memory to 
// put stored tags from the DB.
var tags = [];

// tagsToSubmit is to collect the tags from the TagContainer and pass it to the DB. 
// The best way to do this might be to treat the tag data as structured JSON and make this an object
// which gets passed to the server as a string of base64 data
var tagsToSubmit = {};
var encodedTags = null;

// This variable may be unnecessary - DELETE later if so
var subjectTagCount = 0;
var preExistingSubjects = {};

// flag to indicate if the subjects have been changed at all gets added to POST
// and sent to DB for determining if an UPDATE to subjects needs to occur at all
var changedFlag = false; 
//=============================================================================
// EVENT LISTENER FUNCTIONS
//=============================================================================

// Scan for ENTER in Tag Container entry
//-----------------------------------------------------------------------------
// Function:    subjectDatalistInput.addEventListener('keyup', function(e){...})
// Params:      'keyup' - not a parameter passed in by the invoking function, 
//              but the trigger the event listener looks for.  Whenever the 
//              event occurs, it executes the 'function(e){...}' portion
//
// Desc:        This is an 'active' function in that its attached to an event 
//              listener.  Scans for ENTER in the input field for subjects, 
//              either when a custom subject is added or an existing subject is
//              chosen.
// Invocations: createBook.php
//              updateBook.php
//------------------------------------------------------------------------------
subjectDatalistInput.addEventListener('keyup', function(e){
    if(subjectDatalistInput.value != ''){
        if(e.key === 'Enter'){
            changedFlag = true;
            scanInputContainer();  
            addTags();
            subjectDatalistInput.value = '';
            tagsToSubmit = gatherTagsForSubmit();
            encodedTags = btoa(JSON.stringify(tagsToSubmit));
            
            //encodedTags = base64_encode(tagsToSubmit);
            //console.log('tagsToSubmit: ', encodedTags);
            appendToPost(encodedTags);  
            updateUI();
            
        }
    }    
})


// Remove Tags from Tag Container
//-----------------------------------------------------------------------------
// Function:    document.addEventListener('click', function(e){...})        
// Params:      'click' - not a parameter passed in by the invoking function, 
//              but the trigger the event listener looks for.  Whenever the 
//              event occurs, it executes the 'function(e){...}' portion
//
// Desc:        This is an 'active' function in that its attached to an event 
//              listener.  Scans for mouse CLICKS on the X's of the tags in
//              the tag container of UPDATE and CREATE, and handles removing 
//              it from the 'tags' data object and tag container.  Refreshes
//              the tag container and the tags to be submitted to DB with each
//              removal.
// Invocations: createBook.php
//              updateBook.php
//------------------------------------------------------------------------------
document.addEventListener('click', function(e){
    if(e.target.tagName === 'I'){
        changedFlag = true;
        const value = e.target.getAttribute('data-item');

        // The tag now has meta-data associated with it for 'data-id' and
        // its set to either a SubjectID or 'new'
        // The indexOf () function returns -1 if the value is never found.
        // the problem is that this is no longer just an array of values, but a Javascript Object and this has to be 
        // converted to a string to use JSON.parse() and then using the array may function to get the correct index.
        var item = JSON.parse(JSON.stringify(tags));
        var index = item.map(function(d) { return d['name']; }).indexOf(value);
        //const index = tags.name.indexOf(value) + 1;
        
        tags = [... tags.slice(0, index), ... tags.slice(index + 1)];
        
        // refresh the tag container
        addTags();
        tagsToSubmit = gatherTagsForSubmit();
        encodedTags = btoa(JSON.stringify(tagsToSubmit));
        appendToPost(encodedTags);
        updateUI();
        //subjectTagCount = subjectTagCount - 1;
    }
})

//=============================================================================
// STANDARD FUNCTIONS
//=============================================================================

// the tagJSON passed in is the value in the middle of <span></span> elems
// like 'Javascript', 'foo', 'Computer History'
function createTag(tagJSON){
    const div = document.createElement('div');
    div.setAttribute('class', 'tag');
    const span = document.createElement('span');
    span.innerHTML = tagJSON.name;
    // the <i></i> elems are being used as the close button for some reason
    const closeBtn = document.createElement('i');
    closeBtn.setAttribute('class', 'material-icons');
    closeBtn.setAttribute('data-item', tagJSON.name);
    
    // this adds the ID of the subject to the tag's metadata
    // if the subject is new (not in DB) then the value is 'new' and is parsed out server-side
    closeBtn.setAttribute('data-id', tagJSON.id);
    
    closeBtn.innerHTML = 'close';

    div.appendChild(span);
    div.appendChild(closeBtn);
    return div;
}

//-----------------------------------------------------------------------------
// Function:    resetTags()        
// Params:      
//
// Desc:        clear the tag container when refreshing its content
// Invocations: this.addTags()
//------------------------------------------------------------------------------
function resetTags(){
    document.querySelectorAll('.tag').forEach(function(tag){
        tag.parentElement.removeChild(tag);
    })
}

//-----------------------------------------------------------------------------
// Function:    addTags()        
// Params:      
//
// Desc:        calls resetTags(), loops through the 'tags' object and for each
//              item, create a tag in the container and prepend it to the front 
//              of the container 
// Invocations: this.subjectDatalistInput.addEventListener('keyup', 
//              function(e){...})
//              this.document.addEventListener('click', function()e{...})
//------------------------------------------------------------------------------
function addTags() {
    resetTags();
    
    tags.slice().reverse().forEach(function(tag) {
        const input = createTag(tag);
        tagContainer.prepend(input);
    })
}

// USED IN THE UPDATE PAGE
//-----------------------------------------------------------------------------
// Function:    addPreExistingTags()        
// Params:      
//
// Desc:        load in the pre-existing tags attached to a book and 
//              ids from the DB (which are loaded into the HTML generated by the
//              PHP section of updateBook.php).  Groups the name and ID of the 
//              pre-existing subject into tag JSON and populates the Tag Container
//              adds the pre-existing tags to the 'tags' object so that state
//              is kept consistent as items are added / subtracted.
// Invocations: updateBook.php (included as <script> element at bottom)
//------------------------------------------------------------------------------
function addPreExistingTags() {
    if(subjectDatalistInput.getAttribute('data-value') !== ''){
        let preExistingSubjectsArray = document.getElementById('subject-input').getAttribute('data-value').split(';');
        let preExistingIDsArray = document.getElementById('subject-input').getAttribute('data-id').split(',');

        let preExistingSubjectJSONToPush = [];

        for (var x = 0; x< preExistingSubjectsArray.length; x++){
            preExistingSubjectJSONToPush.push({name: preExistingSubjectsArray[x], id: preExistingIDsArray[x]});
        }

        // need to add the values in the $book->SubjectName which are semi-colon separated
        console.log('line 79: ', preExistingSubjectJSONToPush);

        // loop through the tags object and for each item, create a tag in the container and prepend it 
        // to the front of the container
        preExistingSubjectJSONToPush.slice().reverse().forEach(function(tag) {
            const input = createTag(tag);
            
            // add the pre-existing tags to the tag container for UI purposes
            tagContainer.prepend(input);

            // add pre-existing tags to tag object 
            tags.push(tag);
        })
    }
    updateUI();
}

//-----------------------------------------------------------------------------
// Function:    scanInputContainer()
// Params:      
//
// Desc:        scans input field for entries, constructs JSON & does either:
//              1) push JSON for a subject existing in DB already into 'tags'
//              2) push JSON for a NEW subject into 'tags'
//              existing subject is of form: {name: <name>, id: <id>}
//              NEW subject is of form: {name: <name>, id: 'new'}
//
// Invocations: this.subjectDatalistInput.addEventListener('keyup', 
//              function(e){...})
//------------------------------------------------------------------------------
function scanInputContainer(){
    //WORKING PUSH TO TAGS
    //tags.push(subjectDatalistInput.value);
    
    // What was I planning here? I need to detect whether a tag is coming from the DB or from custom entry.
    var sel = document.getElementById('subject-input');
    var newSubs = {};
    var found = false;
    console.log('sel.value: ', sel.value);
    
    // Loop through the list of subjects and see if the entered subject is one of them
    // if so, add it to the tags object with its ID.  If not add it to tags object with a 'new' value
    for (var i=0; i < datalistTestData.options.length; i++){
        if(datalistTestData.options[i].value == sel.value){
            //console.log('subject data value: ', datalistTestData.options[i].getAttribute('data-value'));
            
            // push the value in the datalist input into the tags object
            existingSubjectData = {name: datalistTestData.options[i].getAttribute('value'), id: datalistTestData.options[i].getAttribute('data-value')}
            if (_.findWhere(tags, existingSubjectData) == null) {
                tags.push(existingSubjectData);
            }
            
            //console.log('existingSubjectData:', existingSubjectData);
            found = true;
        }
    }
    
    // add new subjects to a separate array object to encode and push to the Subject->Create()
    if(found == false){
        newSubjectData = {name: sel.value, id: 'new'};
        if (_.findWhere(tags, newSubjectData) == null){
            tags.push(newSubjectData);
        }
        //console.log('newSubjectData: ', newSubjectData);
    }
}

// Gather Tags For Submit
//-----------------------------------------------------------------------------
// Function:    gatherTagsForSubmit()
// Params:      
//
// Desc:        selects all HTML elements marked '.tag', and constructs a JSON
//              data chunk, which is then pushed into the 'tagsToSubmit' array
//              and returned
// Invocations: this.subjectDatalistInput.addEventListener('keyup', 
//              function(e){...})
//              this.document.addEventListener('click', function(e){...})
//------------------------------------------------------------------------------
function gatherTagsForSubmit(){
    var tagsToSubmit = [];
    var i = 0;  // accumulator
    
    document.querySelectorAll('.tag').forEach(function(tag){
        //str = tag.textContent;
        //str = str.slice(0, -5);
        name = tag.getElementsByTagName('i')[0].getAttribute('data-item');
        id = tag.getElementsByTagName('i')[0].getAttribute('data-id');
        tagData = {name: name, id: id};
        //console.log('tag Name: ', name, '; tag ID: ', id);
        
        tagsToSubmit.push(tagData);
        console.log('tagsToSubmit: ', tagsToSubmit);
        i += 1;
    })  
    subjectTagCount = i;
    console.log('tagsToSubmit: ', tagsToSubmit);
    return tagsToSubmit;
}

//-----------------------------------------------------------------------------
// Function:    appendToPost(tagsToSubmit)        
// Params:      tagsToSubmit - an array of either IDs or the string 'new'
// ADD THE TAGS TO THE POST
//
// Desc:        Append the tags array passed as parameter to the POST of the page
// Invocations: this.subjectDatalistInput.addEventListener('keyup', 
//              function(e){...})
//------------------------------------------------------------------------------
function appendToPost(tagsToSubmit){
    const form = document.querySelector('#mainForm');
    const tagInput = document.createElement('input');
    const tagCount = document.createElement('input');
    const changed = document.createElement('input');
    tagInput.setAttribute('name', 'subject_name');
    tagInput.setAttribute('type', 'hidden');
    tagInput.setAttribute('value', tagsToSubmit);
    tagCount.setAttribute('name', 'subject_count');
    tagCount.setAttribute('type', 'hidden');
    tagCount.setAttribute('value', subjectTagCount);
    changed.setAttribute('name', 'subject_changed');
    changed.setAttribute('type', 'hidden');
    changed.setAttribute('value', changedFlag);
    form.appendChild(tagInput);
    form.appendChild(tagCount);
    form.appendChild(changed);
}

function updateUI(){
    var size = Object.keys(tags).length;

    if(size > 0){
        subjectDatalistInput.setAttribute('placeholder', 'Add more?');
    }
    else{
        subjectDatalistInput.setAttribute('placeholder', 'No Subjects attached to this book yet...');
    }
}
