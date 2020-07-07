// ============================================================
// SHOW NOTIFICATION
//
// Look for GET from the ClearReload.php page and INJECT 
// the message and class type into the notificaiton
// ============================================================
const queryString = window.location.search;
const urlParams = new URLSearchParams(queryString);
let _authorElementId = 'authorElementId';
let _authorElementIterator = 0;
let _authorInputLast = 'LastName';
let _authorInputFirst = 'FirstName';
let _authorInputMiddle01 = 'MiddleName01_';
let _authorInputMiddle02 = 'MiddleName02_';
let _authorInputSuffix = 'Suffix';

if (urlParams.get('bouncedMsg')){
  let notification = document.getElementById('notification');
  notification.style.display = 'block';
  notification.innerHTML = urlParams.get('bouncedMsg');

  if(urlParams.get('bouncedType') === 'success'){
    notification.setAttribute('class', 'alert alert-success');
  }else{
    notification.setAttribute('class', 'alert alert-danger');
  }
}


// ----------------------------------------------------------------------------
// DEALS WITH THE SUBJECT DROP-DOWN MENU
// ----------------------------------------------------------------------------
function toggleField(hideObj,showObj){
    hideObj.disabled=true;        
    hideObj.style.display='none';
    showObj.disabled=false;   
    showObj.style.display='inline';
    showObj.focus();
}

// ----------------------------------------------------------------------------
// TOGGLE FUNCTION FOR ADVANCED SECTION
// ----------------------------------------------------------------------------
let advButton = document.getElementById('advancedToggle');

advButton.addEventListener('click', function(){
    slideToggleTargeted(headContainerEtAlAndComments, 200);
});

// ----------------------------------------------------------------------------
// ADD ANOTHER AUTHOR BUTTON
// ----------------------------------------------------------------------------
let addAuthorButton = document.getElementById('btnAddAnotherAuthor');

addAuthorButton.addEventListener('click', function(){
    addAnotherAuthor();
});

// ----------------------------------------------------------------------------
// REMOVE ADDED AUTHOR BUTTON
// ----------------------------------------------------------------------------
let removeAuthorButton = document.getElementById('btnRemoveAddedAuthor');

removeAuthorButton.addEventListener('click', function(){
    removeAddedAuthorElement();
});

function removeAddedAuthorElement(){
    let mainForm = document.getElementById('mainForm');
    let removeTarget = document.getElementById(_authorElementId + _authorElementIterator);
    mainForm.removeChild(removeTarget);
    let btnRemoveAuthor = document.getElementById('subcontainerRemoveAddedAuthor');
    _authorElementIterator--;
    if(_authorElementIterator === 0){
        btnRemoveAuthor.style.display = 'none';
    }
}

function addAnotherAuthor(){
    _authorElementIterator++;
    // DEFINE THE NEW AUTHOR SECTION
    let newHeadContainerAuthor = document.createElement('DIV');
    newHeadContainerAuthor.setAttribute('class', 'headContainerAuthor');
    newHeadContainerAuthor.setAttribute('id', _authorElementId + _authorElementIterator);
    newHeadContainerAuthor.style.display = 'block';

        let newHeadLabelAuthor = document.createElement('DIV');
        newHeadLabelAuthor.setAttribute('class', 'headLabelAuthor');
        newHeadLabelAuthor.innerHTML = 'Author';

        let newContainerAuthor = document.createElement('DIV');
        newContainerAuthor.setAttribute('class', 'containerAuthor');

            let newSubContainerAuthor = document.createElement('DIV');
            newSubContainerAuthor.setAttribute('class', 'subContainerAuthor');

                let newSubSubContainerAuthorLast = document.createElement('DIV');
                newSubSubContainerAuthorLast.setAttribute('class', 'subSubContainerAuthorLast');
                    let newLabelAuthorLast = document.createElement('DIV');
                    newLabelAuthorLast.setAttribute('class', 'labelAuthorLast');
                    newLabelAuthorLast.innerHTML = 'Last Name';

                    // INPUT LAST
                    let newInputAuthorLast = document.createElement('INPUT');
                    newInputAuthorLast.setAttribute('class', 'inputAuthorLast form-control');
                    newInputAuthorLast.setAttribute('name', _authorInputLast + _authorElementIterator);
                    newInputAuthorLast.setAttribute('type', 'text');
                    newInputAuthorLast.value = '';

                let newSubSubContainerAuthorFirst = document.createElement('DIV');
                newSubSubContainerAuthorFirst.setAttribute('class', 'subSubContainerAuthorFirst');
                    let newLabelAuthorFirst = document.createElement('DIV');
                    newLabelAuthorFirst.setAttribute('class', 'labelAuthorFirst');
                    newLabelAuthorFirst.innerHTML = 'First Name';

                    // INPUT FIRST
                    let newInputAuthorFirst = document.createElement('INPUT');
                    newInputAuthorFirst.setAttribute('class', 'inputAuthorFirst form-control');
                    newInputAuthorFirst.setAttribute('name', _authorInputFirst + _authorElementIterator);
                    newInputAuthorFirst.setAttribute('type', 'text');
                    newInputAuthorFirst.value = '';

                /* INSERT TOGGLE FOR AUTHOR ADVANCED SECTION */
                let newSubContainerAuthorExtra = document.createElement('DIV');
                newSubContainerAuthorExtra.setAttribute('class', 'subContainerAuthorExtra');
                    let newSubSubContainerAuthorMiddle01 = document.createElement('DIV');
                    newSubSubContainerAuthorMiddle01.setAttribute('class', 'subSubContainerMiddle01');
                        let newLabelAuthorMiddle01 = document.createElement('DIV');
                        newLabelAuthorMiddle01.setAttribute('class', 'labelAuthorMiddle01');
                        newLabelAuthorMiddle01.innerHTML = 'Author Middle 01';

                        // INPUT MIDDLE01
                        let newInputAuthorMiddle01 = document.createElement('INPUT');
                        newInputAuthorMiddle01.setAttribute('class', 'inputAuthorMiddle01 form-control');
                        newInputAuthorMiddle01.setAttribute('name', _authorInputMiddle01 + _authorElementIterator);
                        newInputAuthorMiddle01.setAttribute('type', 'text');
                        newInputAuthorMiddle01.value = '';
                        
                    let newSubSubContainerAuthorMiddle02 = document.createElement('DIV');
                    newSubSubContainerAuthorMiddle02.setAttribute('class', 'subSubContainerMiddle02');
                        let newLabelAuthorMiddle02 = document.createElement('DIV');
                        newLabelAuthorMiddle02.setAttribute('class', 'labelAuthorMiddle02');
                        newLabelAuthorMiddle02.innerHTML = 'Author Middle 02';

                        // INPUT MIDDLE02
                        let newInputAuthorMiddle02 = document.createElement('INPUT');
                        newInputAuthorMiddle02.setAttribute('class', 'inputAuthorMiddle02 form-control');
                        newInputAuthorMiddle02.setAttribute('name', _authorInputMiddle02 + _authorElementIterator);
                        newInputAuthorMiddle02.setAttribute('type', 'text');
                        newInputAuthorMiddle02.value = '';

                    let newSubSubContainerAuthorSuffix = document.createElement('DIV');
                    newSubSubContainerAuthorSuffix.setAttribute('class', 'subSubContainerSuffix');
                        let newLabelAuthorSuffix = document.createElement('DIV');
                        newLabelAuthorSuffix.setAttribute('class', 'labelAuthorSuffix');
                        newLabelAuthorSuffix.innerHTML = 'Suffix';

                        // INPUT SUFFIX
                        let newInputAuthorSuffix = document.createElement('INPUT');
                        newInputAuthorSuffix.setAttribute('class', 'inputAuthorSuffix form-control')
                        newInputAuthorSuffix.setAttribute('name', _authorInputSuffix + _authorElementIterator);
                        newInputAuthorSuffix.setAttribute('type', 'text');
                        newInputAuthorSuffix.setAttribute('placeholder', 'Jr., Sr., III., Esq., Ph.D, etc');
                        newInputAuthorFirst.value = '';

    // ASSEMBLE THE NEW AUTHOR SECTION
    newHeadContainerAuthor.appendChild(newHeadLabelAuthor);
    newHeadContainerAuthor.appendChild(newContainerAuthor);
        newContainerAuthor.appendChild(newSubContainerAuthor);
            newSubContainerAuthor.appendChild(newSubSubContainerAuthorLast);
                newSubSubContainerAuthorLast.appendChild(newLabelAuthorLast);
                newSubSubContainerAuthorLast.appendChild(newInputAuthorLast);
            newSubContainerAuthor.appendChild(newSubSubContainerAuthorFirst);
                newSubSubContainerAuthorFirst.appendChild(newLabelAuthorFirst);
                newSubSubContainerAuthorFirst.appendChild(newInputAuthorFirst);
        newContainerAuthor.appendChild(newSubContainerAuthorExtra);
            newSubContainerAuthorExtra.appendChild(newSubSubContainerAuthorMiddle01);
                newSubSubContainerAuthorMiddle01.appendChild(newLabelAuthorMiddle01);
                newSubSubContainerAuthorMiddle01.appendChild(newInputAuthorMiddle01);
            newSubContainerAuthorExtra.appendChild(newSubSubContainerAuthorMiddle02);
                newSubSubContainerAuthorMiddle02.appendChild(newLabelAuthorMiddle02);
                newSubSubContainerAuthorMiddle02.appendChild(newInputAuthorMiddle02);
            newSubContainerAuthorExtra.appendChild(newSubSubContainerAuthorSuffix);
                newSubSubContainerAuthorSuffix.appendChild(newLabelAuthorSuffix);
                newSubSubContainerAuthorSuffix.appendChild(newInputAuthorSuffix);

    // APPEND NEW AUTHOR SECTION BELOW THE INITIAL ONE
    let mainForm = document.getElementById('mainForm');
    let inserTarget = document.getElementById('subcontainerAddAnotherAuthor');
    mainForm.insertBefore(newHeadContainerAuthor, inserTarget);

    let btnRemoveAuthor = document.getElementById('subcontainerRemoveAddedAuthor');
    btnRemoveAuthor.style.display = 'block';
}

/*-----------------------------------------------------------------------------
// LISTENER FOR PROGRESS INPUT SECTION
// Function:    (auto-executing function)()        
//
// Desc:        display progress, progressBar, ONLY if there's a non-0 value
//              in the PageCount box
// Invocations: createBook.php
//---------------------------------------------------------------------------*/
(function (){
    var pageCount = document.getElementById('PageCount');
    pageCount.addEventListener('keyup', displayProgressField);
})();

(function (){
    var progressInput = document.getElementById('inputProgress');
    progressInput.addEventListener('keyup', updateProgressBar);
})();

// ------------------------------------------------------------
// DISPLAY PROGRESS CONTAINER
// ------------------------------------------------------------
function displayProgressField(){
    var progress = document.getElementById('containerProgress');
    var headContainerStartReading = document.getElementById('headContainerStartReading');
    var progressPlaceholder = document.getElementById('containerProgressPlaceholder');
    var containerProgressBar = document.getElementById('containerProgressBar');
    var containerReadingAndProgress = document.getElementById('containerReadingAndProgress');
    var progressBar = document.getElementById('progressBar');
    var pageCountLabel = document.getElementById('pageCount-label');
    var pageCountValue = document.getElementById('PageCount').value;

    // TURN ON
    if(this.value > 0){
        containerProgressBar.style.display = 'block';
        progress.style.display = 'flex';    
        progressPlaceholder.style.display = 'none';
        containerReadingAndProgress.style.setProperty('width', '100%');
        headContainerStartReading.style.setProperty('border-right', 'none');
        pageCountLabel.innerHTML = pageCountValue;
        progressBar.setAttribute('max', pageCountValue);
    }
    // TURN OFF
    else{
        
        headContainerStartReading.style.setProperty('border-right', '1px solid #ccc');
        progress.style.display = 'none';        
        progressPlaceholder.style.display = 'flex';
        containerReadingAndProgress.style.setProperty('width', '50%');
        headContainerStartReading.style.setProperty('border-right', '1px solid #ccc');
        containerProgressBar.style.display = 'none';
    }

}

// ------------------------------------------------------------
// DISPLAY PROGRESS BAR
// ------------------------------------------------------------
function updateProgressBar(){
    
    var progressBar = document.getElementById('progressBar');
    var pageCountLabel = document.getElementById('pageCount-label');
    
    var pageCountValue = document.getElementById('PageCount').value;
    var progressValue = document.getElementById('inputProgress').value;
    
    // TURN ON
    if(this.value > 0){
        // progressBar.setAttribute('max', pageCountValue);
        progressBar.setAttribute('value', progressValue);
        pageCountLabel.innerHTML = pageCountValue;
    }
    // TURN OFF
    else{
        // progressBar.setAttribute('max', '');
        progressBar.setAttribute('value', '');   
    }
    console.log(progressBar.getAttribute('value'));
    
}

/*-----------------------------------------------------------------------------
// Function:    attachReadingToPost()        
//
// Desc:        Javascript. Get the IDs of selected Books & pass them to $_POST  
// Invocations: createBook.php
//---------------------------------------------------------------------------*/
function attachReadingToPost(){
    var readingValue = document.querySelector('#reading-box').checked;
    if(readingValue){
        readingValue = 1;
    }else{
        readingValue = 0;
    }
    const form = document.querySelector('#mainForm');
    const readingInputToAttach = document.createElement('input');

    readingInputToAttach.setAttribute('name', 'Reading');
    readingInputToAttach.setAttribute('type', 'hidden');
    readingInputToAttach.setAttribute('value', readingValue);

    form.appendChild(readingInputToAttach);
}