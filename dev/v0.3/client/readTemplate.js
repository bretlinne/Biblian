// ============================================================================
// readingTemplate.js
// ============================================================================
_currentBtnEvent = null;
_mode = null;

// ============================================================
// SHOW NOTIFICATION
//
// Look for GET from the ClearReload.php page and INJECT 
// the message and class type into the notificaiton
// ============================================================
const queryString = window.location.search;
const urlParams = new URLSearchParams(queryString);

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

const dateFinishedLabels = document.getElementsByClassName('dateFinishedLabel');
for (let i = 0; i < dateFinishedLabels.length; i++) {
    const dateFinishedLabel = dateFinishedLabels[i];
}
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

// /* ----------------------------------------------------------------------------
// // Function:    statusContainerAction(obj)      
// //
// // Params:      obj - is usually the button itself, passed into the function 
// //                    to make it easier to manipulate elements of the button.
// //
// // Desc:        swaps the UI representation of the status of a book between
// //              READING, UNREAD, FINISHED
// //              
// // Invocations: readTemplate.php
// //              --if($DateFinished){
//                     echo "<button class='statusFin' statusValue='finished' 
//                       onclick='statusContainerAction(this)'>";
//                   }else if ($Reading){
//                     echo "<button class='statusReading' statusValue='reading' 
//                       onclick='statusContainerAction(this)'>";
//                   }else{
//                     echo "<button class='statusUnread' statusValue='unread' 
//                       onclick='statusContainerAction(this)'>";
//                   }
// // --------------------------------------------------------------------------*/
// function statusContainerAction(obj){
//     const status = obj.getAttribute('statusValue');
//     console.log('obj.statusValue B4: ', status);
//     if (status === 'finished'){
//         //obj.innerHTML = imgUnread;
//         obj.setAttribute('statusValue', 'unread');
//         obj.setAttribute('class', 'statusUnread');
//     }else if(status === 'reading'){
//         //obj.innerHTML = imgFinished;
//         //obj.value = 'finished';
//         obj.setAttribute('statusValue', 'finished');
//         obj.setAttribute('class', 'statusFin');
//     }else{
//         //obj.innerHTML = imgReading;
//         //obj.value = 'reading';
//         obj.setAttribute('statusValue', 'reading');
//         obj.setAttribute('class', 'statusReading');
//     }
//     console.log('obj.statusValue: AFTR', status);
// }

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


/*-----------------------------------------------------------------------------
// Function:    deleteSelected()        
//
// Params:      -
// Desc:        Get the IDs of selected Books & pass them to $_POST  
// Invocations: readtemplate.php:
//              --echo "<button class='btn btn-primary' id='btnDeleteSelected' 
//                  onclick='JavaScript:return deleteSelected();'>";
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
// AJAX Actions
//
// ============================================================================
// --------------------------------------------
// AJAX for Progress Input
// --------------------------------------------
var xhr = createXHR();

function createXHR(){
  var xhr;
    if(window.ActiveXObject){
        try{
            xhr = new ActiveXObject('Microsoft.XMLHTTP');
        }catch(e){
            xhr = false;
        }
    }else{
        try{
            xhr = new XMLHttpRequest();
        }catch(e){
            xhr = false;
        }
    }
    if(!xhr){
        alert("Can't create the XHR object!");
    }
    else{
        return xhr;
    }
}

/* ----------------------------------------------------------------------------
// Function:    readingListSwitchAction(obj)   
//
// Params:      obj - this is the button event that called this function
//
// Desc:        toggle the status between OnList and Not On List. 
//              Make Ajax call & send the data to the server to UPDATE the DB.
// READING LIST SWITCH FUNCTIONALITY
// --Toggles whether a book is on the reading list or not
// --handles UI changes 
// --at least starts the process of an async DB operation to update the DB
// immmediately with the change.
// --perhaps have the status image only change when a successful read of the 
// DB confirms that the 'reading' value is changed.
// --------------------------------------------------------------------------*/
function readingListSwitchAction(e){
    let bookId = e.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode.childNodes[0].childNodes[0].getAttribute('book-id');
    let readingStatusImage = e.parentNode.parentNode.parentNode.childNodes[1];
    if(e.checked){
        readingStatusImage.setAttribute('class', 'toggleStateImageFinished');
        reading = 1;
    }else{
        readingStatusImage.setAttribute('class', 'toggleStateImageFaded');
        reading = 0;
    }
  
    // call AJAX processing
    _mode = 'reading';
    _currentBtnEvent = e;
  
    processReadingListToggle(bookId, reading);

}

/* ----------------------------------------------------------------------------
// Function:    processReadingListToggle(id, reading)      
//
// Params:      
// Desc:        ajax call to send Finished update to server
//              
// Invocations: readTemplate.js 
//              --function readingListSwitchAction(e){...}
// --------------------------------------------------------------------------*/
function processReadingListToggle(id, reading){
    // check if XHR is busy or ready to connect
    if(xhr.readyState==0 || xhr.readyState == 4){
      // send that data to the server-side
      let urlString = './readTemplateAjax.php?mode=' + _mode + '&id=' + id + '&reading=' + reading;

      xhr.open('GET', urlString, true);
      
      // call this to set the DateFinished label below the Finished status button
      xhr.onreadystatechange = handleServerResponseReadingListToggle;
      
      xhr.send(null);
    }
    else{
      // wait a second and call itself again
      setTimeout('processReadingListToggle()', 1000);
    }
}

// update the status based on DB response
function handleServerResponseReadingListToggle(){
    if(this.readyState == 4){
        if(xhr.status == 200){
            msg = xhr.responseXML.documentElement.firstChild.data;
            // console.log('finishedToggle resp: ', msg);

            // GET STATUS BUTTON's DataFinished value
            var onListImage = _currentBtnEvent.parentNode.parentNode.parentNode.parentNode.childNodes[0].childNodes[1];
            var statusImage = _currentBtnEvent.parentNode.parentNode.parentNode.parentNode.childNodes[2].firstChild;
            var advNode = _currentBtnEvent.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode.childNodes[1];
            var dateFinished = advNode.childNodes[1].childNodes[3].getAttribute('value');
            var progress = advNode.childNodes[1].childNodes[4].getAttribute('value');    
            
            console.log('statusImage: ', statusImage);

            if (msg === '1'){
                onListImage.setAttribute('class', 'toggleStateImageFinished');
                
                if (dateFinished === ''){
                    statusImage.setAttribute('statusvalue', 'reading');
                    statusImage.setAttribute('class', 'statusReading');
                }else{
                    statusImage.setAttribute('statusvalue', 'finished')
                    statusImage.setAttribute('class', 'statusFin');
                }

            }else if (msg === '0'){
                onListImage.setAttribute('class', 'toggleStateImageFaded');
                
                if (dateFinished === ''){
                    if (progress > 0){
                        statusImage.setAttribute('statusvalue', 'started');
                        statusImage.setAttribute('class', 'statusStarted');
                    }else if (progress === 0 || progress === ''){
                        statusImage.setAttribute('statusvalue', 'unread');
                        statusImage.setAttribute('class', 'statusUnread');
                    }
                }else{
                    statusImage.setAttribute('statusvalue', 'finished')
                    statusImage.setAttribute('class', 'statusFin');
                }
            }
            
            xhr.abort();
        }else{
            console.log('readyState: ', xhr.readyState);
            alert('Something went wrong');
        }
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