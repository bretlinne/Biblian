addPreExistingTags();
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
  console.log(notification);
  if(urlParams.get('bouncedType') === 'success'){
    notification.setAttribute('class', 'alert alert-success');
  }else{
    notification.setAttribute('class', 'alert alert-danger');
  }
}

/*-----------------------------------------------------------------------------
// Function:    toggleField(hideObj, showObj)        
//
// Desc:        DEALS WITH THE SUBJECT DROP-DOWN MENU
// Invocations: ??? 
//                                                  --- THIS MAY BE DEFUNCT ---
//---------------------------------------------------------------------------*/
function toggleField(hideObj,showObj){
    hideObj.disabled=true;        
    hideObj.style.display='none';
    showObj.disabled=false;   
    showObj.style.display='inline';
    showObj.focus();
}

/*-----------------------------------------------------------------------------
// Function:    deleteThisBook()        
//
// Desc:        Javascript. Get the IDs of selected Books & pass them to $_POST  
// Invocations: updateBook.php:
//                  if($book->deleteThisBook($bookIdToDelete)){
//                      header('Location:./index.php');
//                  }
//                  ...
//                  <button id='deleteBtn' book-id='<?php echo $ID ?>' onclick='JavaScript:return deleteThisBook();'>
//                      <span class='glyphicon glyphicon-trash'></span>
//                  </button>
//---------------------------------------------------------------------------*/
function deleteThisBook(){
    // GET THE ID OF THE BOOK(s) SELECTED BY CHECKBOXEN - bookCheckBox.book-id
    var bookId = document.getElementById('deleteBtn').getAttribute('book-id');
    
    // Proceed with DELETE
    // only _POST data IF there are selected checkboxen
    if (confirm('Are you ABSOLUTELY sure you want to permanently delete this book from your Library?')){
        const btnSubmission = document.querySelector('#deleteForm');
        const idToDelete = document.createElement('input');
        idToDelete.setAttribute('name', 'bookId');
        idToDelete.setAttribute('type', 'hidden');
        idToDelete.setAttribute('value', bookId);
        btnSubmission.appendChild(idToDelete);
        document.getElementById('deleteForm').submit();
        return(true);
    }
    return(false); 
}

/*-----------------------------------------------------------------------------
// Function:    attachReadingToPost()        
//
// Desc:        Javascript. Get the IDs of selected Books & pass them to $_POST  
// Invocations: updateBook.php
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
