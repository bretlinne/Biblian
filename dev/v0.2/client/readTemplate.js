// ============================================================================
// readingTemplate.js
// ============================================================================
// ------------------------------------------------------------------------
// STATUS CONTAINER FUNCTIONALITY
// ------------------------------------------------------------------------
/*
// basic formula for creating a JS button:
// 1) get a handle for the button through querySelector and assigning ther DOM element to a JS alias (a const)
const statContainer = document.getElementsByClassName('.statusContainer');
// 2) attach an event listener to the DOM element.  In this case its mouseup.  
statContainer.addEventListener('mouseup', function(e){
    statusValue = statContainer.getAttribute('status-value');
    alert(statusValue);
    // 3) tell it what to do
    
*/
// If I want each button in the HTML page to have ownership of the function calling it, then the function needs
// to be called from the HTML.  In other words, onclick='statusContainerAction(this)'.  This calls the
// statusContainterAction specifically for each instance of the button and only for that instance.

/* ----------------------------------------------------------------------------
// Function:    statusContainerAction(obj)      
//
// Params:      obj - is usually the button itself, passed into the function 
//                    to make it easier to manipulate elements of the button.
//
// Desc:        swaps the UI representation of the status of a book between
//              READING, UNREAD, FINISHED
//              
// Invocations: readTemplate.php
//              --if($DateFinished){
                    echo "<button class='statusFin' statusValue='finished' 
                      onclick='statusContainerAction(this)'>";
                  }else if ($Reading){
                    echo "<button class='statusReading' statusValue='reading' 
                      onclick='statusContainerAction(this)'>";
                  }else{
                    echo "<button class='statusUnread' statusValue='unread' 
                      onclick='statusContainerAction(this)'>";
                  }
// --------------------------------------------------------------------------*/
function statusContainerAction(obj){
    const status = obj.getAttribute('statusValue');
    console.log('obj.statusValue B4: ', status);
    if (status === 'finished'){
        //obj.innerHTML = imgUnread;
        obj.setAttribute('statusValue', 'unread');
        obj.setAttribute('class', 'statusUnread');
    }else if(status === 'reading'){
        //obj.innerHTML = imgFinished;
        //obj.value = 'finished';
        obj.setAttribute('statusValue', 'finished');
        obj.setAttribute('class', 'statusFin');
    }else{
        //obj.innerHTML = imgReading;
        //obj.value = 'reading';
        obj.setAttribute('statusValue', 'reading');
        obj.setAttribute('class', 'statusReading');
    }
    console.log('obj.statusValue: AFTR', status);
}

/* ----------------------------------------------------------------------------
// Function:    navToUpdate(btnEvent)      
//
// Params:      btnEvent - event to run processing on.  Required as the link to
//              nav to Update View is based on getting the ID of the book  
//              chosen int he Read View.  
//
// Desc:        Navigate to the UPDATE View and send the appropriate book ID
//              
// Invocations: readTemplate.php
//              --echo "<div class='navToUpdatePageContainer'>";
// --------------------------------------------------------------------------*/
function navToUpdate(btnEvent){
    bookId = btnEvent.getAttribute('book-id');
    window.location.replace(`./updateBook.php?id=${bookId}`);
}


//----------------------------------------------------
// Collapsible Details Section
// ---------------------------------------------------

let slideUp = (target, duration=500) => {
    target.style.transitionProperty = 'height margin padding';
    target.style.transitionDuration = duration + 'ms';
    target.style.boxSizing = 'border-box';
    target.style.height = target.offsetHeight + 'px';
    target.offsetHeight;

    target.style.height = 0;
    target.style.paddingTop = 0;
    target.style.paddingBottom = 0;
    target.style.marginTop = 0;
    target.style.marginBottom = 0;
    target.style.overflow = 'hidden';

    window.setTimeout(() => {
        target.style.display = 'none';
        target.style.removeProperty('height');
        target.style.removeProperty('padding-top');
        target.style.removeProperty('padding-bottom');
        target.style.removeProperty('margin-top');
        target.style.removeProperty('margin-bottom');
        target.style.removeProperty('overflow');
        target.style.removeProperty('transition-duration');
        target.style.removeProperty('transition-property');
    }, duration);
}
let slideDown = (target, duration=500) => {
    target.style.removeProperty('display');
    let display = window.getComputedStyle(target).display;

    if (display === 'none')
      display = 'block';

    target.style.display = display;
    let height = target.offsetHeight;
    target.style.overflow = 'hidden';
    target.style.height = 0;
    target.style.paddingTop = 0;
    target.style.paddingBottom = 0;
    target.style.marginTop = 0;
    target.style.marginBottom = 0;
    target.offsetHeight;
    target.style.boxSizing = 'border-box';
    target.style.transitionProperty = "height, margin, padding";
    target.style.transitionDuration = duration + 'ms';
    target.style.height = height + 'px';
    target.style.removeProperty('padding-top');
    target.style.removeProperty('padding-bottom');
    target.style.removeProperty('margin-top');
    target.style.removeProperty('margin-bottom');
    window.setTimeout( () => {
      target.style.removeProperty('height');
      target.style.removeProperty('overflow');
      target.style.removeProperty('transition-duration');
      target.style.removeProperty('transition-property');
    }, duration);
  }

  var slideToggle = (target, duration = 500) => {
    let adv = target.children[1];

    if (window.getComputedStyle(adv).display === 'none') {
      return slideDown(adv, duration);
    } else {
      return slideUp(adv, duration);
    }
  }


function toggleDetails(e){
    var adv = e.children[1];
    
    if(window.getComputedStyle(adv).display === 'block'){
        hide(adv);      // simple implementation
    }else{
        show(adv);      // simple implementation
    }
}

/*-----------------------------------------------------------------------------
// Function:    deleteSelected()        
//
// Params:      -
// Desc:        Get the IDs of selected Books & pass them to $_POST  
// Invocations: readtemplate.php:
//                echo "<a id='btnDeleteSelected' onclick='deleteSelected()'>";
//---------------------------------------------------------------------------*/
function deleteSelected(){
    // GET THE ID OF THE BOOK(s) SELECTED BY CHECKBOXEN - bookCheckBox.book-id
    var checkboxen = document.getElementsByClassName('bookCheckBox');
    var bookIds = [];
    for (i = 0; i < checkboxen.length; i++){
        if(checkboxen[i].checked){
            bookIds.push(checkboxen[i].getAttribute('book-id'));
        }
    }
    // Proceed with DELETE
    // only _POST data IF there are selected checkboxen
    if(bookIds.length > 0){
        // double check that User wants to delete book from the DB
        if (confirm('Are you ABSOLUTELY sure you want to permanently delete from your Library?')){
            const btnSubmission = document.querySelector('#deleteForm');
            const idsToDelete = document.createElement('input');
            idsToDelete.setAttribute('name', 'bookIds');
            idsToDelete.setAttribute('type', 'hidden');
            idsToDelete.setAttribute('value', bookIds);
            btnSubmission.appendChild(idsToDelete);
            document.getElementById('deleteForm').submit();
            return(true);
        }
    }
    else{
        alert('Select a book to delete first');
        return(false);
    } 
}
// ============================================================================
//
// WORK IN PROGRESS
//
// ============================================================================
/* ----------------------------------------------------------------------------
// Function:    readingListSwitchAction(obj)   
// READING LIST SWITCH FUNCTIONALITY
// --Toggles whether a book is on the reading list or not
// --handles UI changes 
// --at least starts the process of an async DB operation to update the DB
// immmediately with the change.
// --perhaps have the status image only change when a successful read of the 
// DB confirms that the 'reading' value is changed.
// --------------------------------------------------------------------------*/
function readingListSwitchAction(obj){
    //deal with toggling the Currently Reading section
    
    //---------------------------------------
    // deal with stateImage later
    //const toggleStateImage = obj.parentNode.parentNode.childNodes[0];
    //let state = toggleStateImage.getAttribute('state');
    //console.log(state);
    //---------------------------------------
    if (obj.checked === true){
        //toggleStateImage.setAttribute('state', 'on');
        console.log('checked');
    }
    else{
        console.log('off');
    }
}



// // WORK IN PROGRESS TO GET THE DELETE BUTTON TO APPEAR WHEN A CHECKBOX IS CHECKED
// var deleteBtn = document.getElementById('btnDeleteSelected');
// var x = document.getElementsByClassName('bookCheckBox');
// for (i = 0; i < x.length; i++){
//     //x[i].addEventListener('click', function(e){
//         if(x[i].checked){
//             //console.log('false');
//         }
//         else{
//             //console.log('true');
//         }
//         deleteBtn.style.display = 'inline-block';
//     //});    
// };

/*
var slider = document.getElementById('myRange');
var output = document.getElementById('demo');
output.innerHTML = slider.value; // Display the default slider value

// Update the current slider value (each time you drag the slider handle)
slider.oninput = function() {
  output.innerHTML = this.value;
}
*/