// ============================================================================
// readingList.js
//
//
//
// search 'OPTIMIZE'
// ============================================================================

// ============================================================
//
// MODALS 
//
// ============================================================
var modal = document.getElementById("addToListModal");
var modalRating = document.getElementById("updateRatingModal");
// this is the rating value to update
var modalRatingValue = 0;
var _currentBtnEvent = null;
var _currentBtnProgress = null;
var _currentBtnBookId = null;
var _mode = null;

// Get the button that opens the modal
var btn = document.getElementById("btnOpenAddToListModal");
var modalCancel = document.getElementById('modalAddBookCancel');
var modalAccept = document.getElementById('modalAddBookAccept');
var input = document.getElementById('book-input');        // input of the modal

// button on main view, NOT on modal
var btnRemoveFromList = document.getElementById('btnRemoveFromList');

// An array of all rating buttons on the page
var ratingUpdateBtns = document.getElementsByClassName('starsModalLink');
var modalRatingCancel = document.getElementById('modalRatingCancel');
var modalRatingAccept = document.getElementById('modalRatingAccept');

// Get the <span> element that closes the modal
var spanAddToListClose = document.getElementById("closeAddToListModel");
var spanRatingClose = document.getElementById ("closeRatingModel");

// --------------------------------------------
// CLOSE MODAL - CLICK OUTSIDE
// --------------------------------------------
// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
  if (event.target == modal) {
    modal.style.display = "none";
    
  }
  if (event.target == modalRating) {
    modalRating.style.display = 'none';
    modalRating.setAttribute('active', false);
  }
}
// ------------------------------------------------------------
//
// ADD BOOK MODAL
//
// ------------------------------------------------------------
// OPEN MODAL
// ----------------------------
btn.onclick = function() {
  modal.style.display = "block";
}

// --------------------------------------------
// MODAL CANCEL BUTTON - ADD TO LIST
// --------------------------------------------
modalCancel.onclick = function(e) {
  e.preventDefault();
  modal.style.display = 'none';
}

// --------------------------------------
// MODAL ADD BOOK - SUBMIT LOGIC
// --------------------------------------
modalAccept.onclick = function(){
  let bookID = null;
  // GET THE ID OF THE SELECTED BOOK IN MODAL
  for (let i = 0; i < booksInLibrary.options.length; i++) {
    if (booksInLibrary.options[i].value == input.value){
      bookID = booksInLibrary.options[i].getAttribute('id-value');
    }
  }

  // ADD THE ID OF THE BOOK TO ADD TO _POST
  const form = document.querySelector('#modalForm');

  const idToAppendToPost = document.createElement('input');
  idToAppendToPost.setAttribute('name', 'ID');
  idToAppendToPost.setAttribute('type', 'hidden');
  idToAppendToPost.setAttribute('value', bookID);

  const mode = document.createElement('input');
  mode.setAttribute('name', 'mode');
  mode.setAttribute('type', 'hidden');
  mode.setAttribute('value', 'addBook');
          
  form.appendChild(idToAppendToPost);
  form.appendChild(mode);
}


// --------------------------------------------
// CLOSE ADD TO LIST MODAL
// --------------------------------------------
// When the user clicks on <span> (x), close the modal
// This might work for both modals
spanAddToListClose.onclick = function() {
  modal.style.display = "none";
}


// ------------------------------------------------------------
//
// RATING STAR MODAL
//
// ------------------------------------------------------------
// --------------------------------------------
// ASSIGN EACH RATING AN ONCLICK FUNCTION 
// TO OPEN A MODAL
// --------------------------------------------
for (let i = 0; i < ratingUpdateBtns.length; i++) {
  const btn = ratingUpdateBtns[i];
  let bookId = btn.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode.childNodes[0].childNodes[0].getAttribute('book-id');
  
  btn.onclick = function() {
    modalRating.setAttribute('bookId', bookId);
    modalRating.style.display = 'block';
    modalRating.setAttribute('active', true);
  }
}

// --------------------------------------------
// MODAL CANCEL BUTTON - RATING
// --------------------------------------------
modalRatingCancel.onclick = function(e){
  e.preventDefault();
  modalRating.style.display = 'none';
  modalRating.setAttribute('active', false);
}

// --------------------------------------------
// HOVER STATE FOR STAR UPDATE LINK
// --------------------------------------------
for (let i = 0; i < ratingUpdateBtns.length; i++) {
  const ratingBtn = ratingUpdateBtns[i];
  const starsArray = ratingBtn.childNodes;
  
  ratingBtn.addEventListener('mousemove', function hovering(){
    for (let x = 0; x < starsArray.length; x++) {
      const star = starsArray[x];
      switch (star.getAttribute('class')) {
        case 'starImg':
          star.setAttribute('class', 'starImgHover');    
          break;
        case 'starImgHalf':
          star.setAttribute('class', 'starImgHalfHover');
          break;
        case 'starImgEmpty':
          star.setAttribute('class', 'starImgEmptyHover');
          break;
        default:
          break;
      }  
    }
  });
  ratingBtn.addEventListener('mouseout', function exitHover(){
    for (let x = 0; x < starsArray.length; x++) {
      const star = starsArray[x];
      switch (star.getAttribute('class')) {
        case 'starImgHover':
          star.setAttribute('class', 'starImg');    
          break;
        case 'starImgHalfHover':
          star.setAttribute('class', 'starImgHalf');
          break;
        case 'starImgEmptyHover':
          star.setAttribute('class', 'starImgEmpty');
          break;
        default:
          break;
      }
    }
  });  
}

// --------------------------------------------
// MODAL STAR LOGIC
// --------------------------------------------
const modalStarContainer = document.getElementById('modalStarContainer');

const modalStars = document.getElementById('modalStarContainer').children;

for (let i = 0; i < modalStars.length; i++) {
  // ----------------------------------------------------------
  // DEAL WITH SETTING STAR STATES BEFORE OR AFTER CURRENT STAR
  // ----------------------------------------------------------
  modalStars[i].addEventListener('mouseenter', function enterStar(e){
    switch (i) {
      case 0:
        // SET ALL OTHER STARS TO EMPTY
        for (let i = 1; i < modalStars.length; i++) {
          modalStars[i].setAttribute('class', 'modalStarEmpty');
        }
        break;
      case 1:
        // SET 1 STAR BEHIND TO WHOLE; SET 3 STARS AHEAD TO EMPTY
        modalStars[0].setAttribute('class', 'modalStar');
        for (let i = 2; i < modalStars.length; i++) {
          modalStars[i].setAttribute('class', 'modalStarEmpty');
        }
        break;
      case 2:
        // SET 2 STAR BEHIND TO WHOLE; SET 2 STARS AHEAD TO EMPTY
        modalStars[0].setAttribute('class', 'modalStar');
        modalStars[1].setAttribute('class', 'modalStar');
        for (let i = 3; i < modalStars.length; i++) {
          modalStars[i].setAttribute('class', 'modalStarEmpty');
        }
        break;
      case 3:
        // SET 3 STAR BEHIND TO WHOLE; SET 1 STARS AHEAD TO EMPTY
        for (let i = 0; i < modalStars.length-2; i++) {
          modalStars[i].setAttribute('class', 'modalStar');
        }
        modalStars[4].setAttribute('class', 'modalStarEmpty');
        break;
      case 4:
        // SET ALL STARS BEHIND TO WHOLE
        for (let i = 0; i < modalStars.length-1; i++) {
          modalStars[i].setAttribute('class', 'modalStar');
        }
        break;
      default:
        break;
    }
  });
  // ----------------------------------------------------------
  // detect if the mouse is hovering over each star
  // ----------------------------------------------------------
  modalStars[i].addEventListener('mousemove', function hovering(e){
    const starBox = modalStars[i].getBoundingClientRect();
    const star = modalStars[i];
    let maxX = starBox.x + starBox.width;
    // 'e' is the mouse event.  Contains clientX, clientY, screenX, screenY
    let currX = e.clientX;
    
    if(currX < (starBox.x + (starBox.width / 2))){
      star.setAttribute('class', 'modalStarHalf');
    } else if (currX > (starBox.x + (starBox.width /2))) {
      star.setAttribute('class', 'modalStar');
    } else if (currX < starBox.x){
      star.setAttribute('class', 'modalStarEmpty');
    }
  });
  // ----------------------------------------------------------
  // LOOK FOR WHEN THE MOUSE EXITS THE BOUNDS OF ANY STAR
  // ----------------------------------------------------------
  modalStars[i].addEventListener('mouseout', function exitStar(e){
    const starBox = modalStars[i].getBoundingClientRect();
    const star = modalStars[i];
    let leftEdge = starBox.x;
    // let rightEdge = starBox.x + starBox.width;
    if (e.clientX < (leftEdge)){
      star.setAttribute('class', 'modalStarEmpty');
    }
  });
} // END FOR LOOP 

// --------------------------------------
// MODAL RATING SUBMIT LOGIC
// --------------------------------------
modalRatingAccept.onclick = function(){
  // GET THE RATING TO SEND
  // --------------------------
  const modalStars = document.getElementById('modalStarContainer').children;
  for (let i = 0; i < modalStars.length; i++) {
    let star = modalStars[i];
    
    if(star.getAttribute('class') === 'modalStar'){
      modalRatingValue += 1;
    } else if (star.getAttribute('class') === 'modalStarHalf'){
      modalRatingValue += 0.5;
    }
  }
  
  // ADD THE ID OF THE BOOK TO ADD TO _POST
  const form = document.querySelector('#modalStarForm');
  const bookId = modalRating.getAttribute('bookId');
  
  const ratingToAppendToPost = document.createElement('input');
  ratingToAppendToPost.setAttribute('name', 'modalRatingValue');
  ratingToAppendToPost.setAttribute('type', 'hidden');
  ratingToAppendToPost.setAttribute('value', modalRatingValue);

  const mode = document.createElement('input');
  mode.setAttribute('name', 'mode');
  mode.setAttribute('type', 'hidden');
  mode.setAttribute('value', 'updateRating');
          
  const bookIdToAppend = document.createElement('input');
  bookIdToAppend.setAttribute('name', 'bookId');
  bookIdToAppend.setAttribute('type', 'hidden');
  bookIdToAppend.setAttribute('value', bookId);
          
  form.appendChild(ratingToAppendToPost);
  form.appendChild(mode);
  form.appendChild(bookIdToAppend);
}

// --------------------------------------------
// CLOSE RATING MODAL
// --------------------------------------------
// When the user clicks on <span> (x), close the modal
// This might work for both modals
spanRatingClose.onclick = function() {
  modalRating.style.display = "none";
  modalRating.setAttribute('active', false);
}


// // Get the values for the progress labels
// var progressBarLabel = document.getElementsByClassName('progress-bar-label');
// // get the data value of each progress label
// // assign the position relative to the data value
// // create a function that replaces the text display of the div with a percentage of progress

// for (let i = 0; i < progressBarLabel.length; i++) {
//   let width = progressBarLabel[i];
//   // progressBarLabel[i].style.marginLeft = '10px';
//   progress = progressBarLabel[i].getAttribute('data-value');
//   console.log('width: ', width);  
// }










// ------------------------------------------------------------
// Remove Button Logic
// CHANGE - doesn't work for multiple removes
// ------------------------------------------------------------
btnRemoveFromList.onclick = function(){
  // GET ID OF SELECTED BOOK(s) FROM CHECKBOXEN
  var checkboxen = document.getElementsByClassName('bookCheckBox');
  
  var bookIds = [];
  /* GET TITLE AS WELL FOR MULTIPLE UPDATES USING "ON DUPLICATEKEY" */
  var titles = [];
  
  for (i = 0; i < checkboxen.length; i++){
      if(checkboxen[i].checked){
          bookIds.push(checkboxen[i].getAttribute('book-id'));
          titles.push(checkboxen[i].getAttribute('book-title'));
      }
  }
  
  // Proceed with REMOVE
  // only _POST data IF there are selected checkboxen
  if(bookIds.length > 0){
      // double check that User wants to REMOVE book from the list
      if (confirm('Sure you want to remove this book from the Reading List?')){
          const btnSubmission = document.querySelector('#removeForm');

          const idsToRemove = document.createElement('input');
          idsToRemove.setAttribute('name', 'bookIds');
          idsToRemove.setAttribute('type', 'hidden');
          idsToRemove.setAttribute('value', bookIds);
          
          const mode = document.createElement('input');
          mode.setAttribute('name', 'mode');
          mode.setAttribute('type', 'hidden');
          mode.setAttribute('value', 'removeBooks');
          
          const titlesToSend = document.createElement('input');
          titlesToSend.setAttribute('name', 'titles');
          titlesToSend.setAttribute('type', 'hidden');
          titlesToSend.setAttribute('value', titles);

          btnSubmission.appendChild(idsToRemove);
          btnSubmission.appendChild(mode);
          btnSubmission.appendChild(titlesToSend);
          document.getElementById('removeForm').submit();
          return(true);
      }
  }
  else{
      alert('Select a book to remove first!');
      return(false);
  }
}

// ============================================================================
//
// Collapsible Details Section
//
// ============================================================================
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


// ------------------------------------------------------------
// Navigate to UPDATE View
// ------------------------------------------------------------
function navToUpdate(btnEvent){
  bookId = btnEvent.getAttribute('book-id');
  window.location.replace(`./updateBook.php?id=${bookId}`);
}
// ============================================================================
//
// Status Container Actions
//
// ============================================================================
/*-----------------------------------------------------------------------------
// Function:    statusContainerAction(obj)        
//
// Params:      obj - this is the button event that called this function
//
// Desc:        toggle the status between Reading and Finished. Make Ajax call
//              and send the data to the server to UPDATE the DB.
//
// Invocations: readlingList.php
//              --echo "<button class='statusFin' statusValue='finished' 
//                  onclick='statusContainerAction(this)'>";
//---------------------------------------------------------------------------*/
function statusContainerAction(obj){
  let bookId = obj.parentNode.parentNode.parentNode.parentNode.childNodes[0].childNodes[0].getAttribute('book-id');
  const status = obj.getAttribute('statusValue');
  _mode = 'finished';
  let finished = null;
  
  if(status === 'reading'){
      obj.setAttribute('statusValue', 'finished');
      obj.setAttribute('class', 'statusFin');
      finished = 1;
  }else{
      obj.setAttribute('statusValue', 'reading');
      obj.setAttribute('class', 'statusReading');
      finished = 0;
  }
  _currentBtnEvent = obj;

  processFinishedToggle(bookId, finished);
}

/* ----------------------------------------------------------------------------
// Function:    finishedSwitchAction(obj)   
// FINISHED SWITCH FUNCTIONALITY
// --Toggles whether a book is FINISHED or not
// --handles UI changes 
// --at least starts the process of an async DB operation to update the DB
// immmediately with the change.
// --perhaps have the status image only change when a successful read of the 
// DB confirms that the 'reading' value is changed.
// COMMENTED OUT - DECIDED THAT I DIDN"T NEED THE TOGGLE SWITCH
// --------------------------------------------------------------------------*/
// function finishedSwitchAction(obj){
//   //deal with toggling the Currently Reading section
  
//   //---------------------------------------
//   // deal with stateImage later
//   //const toggleStateImage = obj.parentNode.parentNode.childNodes[0];
//   //let state = toggleStateImage.getAttribute('state');
//   //console.log(state);
//   //---------------------------------------
//   if (obj.checked === true){
//       //toggleStateImage.setAttribute('state', 'on');
//       console.log('checked');
//   }
//   else{
//       console.log('off');
//   }
// }

// ============================================================================
//
// Progress Input Section
//
// ============================================================================

/*-----------------------------------------------------------------------------
// Function:    (auto-executing function)()        
//
// Desc:        display buttons and input field for progress ONLY if there is 
//              a PageCount
// Invocations: readlingList.php
//---------------------------------------------------------------------------*/
(function (){
  var progressContainers = document.getElementsByClassName('progress-container');
  for (let i = 0; i < progressContainers.length; i++) {
    const elem = progressContainers[i];
    let bookIdNode = elem.parentNode.parentNode.parentNode.parentNode.childNodes[0].childNodes[0];
    let bookId = bookIdNode.getAttribute('book-id');
    let pageCount = elem.childNodes[2].childNodes[1].getAttribute('value');
    let progressValue = elem.childNodes[3].childNodes[0].getAttribute('value');
    let progressStandIn = elem.parentNode.childNodes[2];
    let pageCountUpdateButton = elem.parentNode.childNodes[3];
    let dateStartedLabel = elem.childNodes[3].childNodes[4];
    let dateStartedValue = dateStartedLabel.getAttribute('value');
    
    // TURN OFF DISPLAY OF PROGRESS BAR AND INPUTS IF NO PAGECOUNT
    if(!pageCount){
      elem.style.display = 'none';
    }else{
      dateStartedLabel.style.display = 'none';
      progressStandIn.style.display = 'none';
      pageCountUpdateButton.style.display = 'none';
      if(!progressValue || progressValue < 1){
        dateStartedLabel.style.display = 'none';
      }else{
        dateStartedLabel.style.display = 'block';
        dateStartedLabel.innerHTML = 'Started: ' + dateStartedValue;
      }
    }
  }
  // progress.style.display = 'none';
})();

/* ----------------------------------------------------------------------------
// Function:    updateProgress(updateEvent)
//
// Params:      updateEvent - event of UI element used to call this function
//
// Desc:        Ajax call to send updates to server on Progress
//              
// Invocations: readingList.php 
//              --echo "<input type='number' class='progress-output' 
//                  onkeyup='updateProgress(this)' min=0 max={$PageCount} 
//                  value={$Progress}></input>";
// --------------------------------------------------------------------------*/
function updateProgress(updateEvent){
  var bookIdNode = updateEvent.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode.childNodes[0].childNodes[0];
  var bookId = bookIdNode.getAttribute('book-id');
  let dateStartedNode = updateEvent.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode.childNodes[1].childNodes[1].childNodes[0];
  console.log('dateStartedNode: ', dateStartedNode);

  let newlyStarted = 1;
  // if there exists a DateStarted, then newlyStarted is false
  if (dateStartedNode.getAttribute('value')){
    newlyStarted = 0; // false means that the book was started before
  }
  
  // set the progressOutput to 0 if there's a null
  let progressOutput = null;
  if (updateEvent.value){
    progressOutput = updateEvent.value;
  } else{
    progressOutput = 0;
  }
  // console.log('progressOutput: ', progressOutput);

  _currentBtnEvent = updateEvent;
  _currentBtnProgress = progressOutput;
  _currentBtnBookId = bookId;
  // call AJAX processing
  _mode = 'progress';
  processProgressUpdate(newlyStarted);
}

/* ----------------------------------------------------------------------------
// Function:    incrementProgress(btnEvent, amount)
//
// Params:      btnEvent - event of UI button used to call this function
//              amount - discrete increment used to increase
//
// Desc:        Ajax call to send updates to server on Progress
//              
// Invocations: readingList.php 
//              --echo "<button class='btn progress-plus1' onclick=
//                    'incrementProgress(this, {$Progress}, 1)'>";
//              --echo "<button class='btn progress-plus5' onclick=
//                    'incrementProgress(this, {$Progress}, 5)'>";
//              --echo "<button class='btn progress-plus10' onclick=
//                    'incrementProgress(this, {$Progress}, 10)'>";
// --------------------------------------------------------------------------*/
function incrementProgress(btnEvent, amount){
  // GET BOOK ID
  var bookIdNode = btnEvent.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode.childNodes[0].childNodes[0];
  var bookId = bookIdNode.getAttribute('book-id');
  let dateStartedNode = btnEvent.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode.childNodes[1].childNodes[1].childNodes[0];
  
  let newlyStarted = 1; // true means the book is just now being started
  
  // if there exists a DateStarted, then newlyStarted is false
  if (dateStartedNode.getAttribute('value')){
    newlyStarted = 0; // false means that the book was started before
  }

  // NOTE - the attribute 'value' is stored in the DOM as a string.  It 
  // therefore must be parsed or cast to an integer to send to the DB
  currentProgress = parseInt(btnEvent.parentNode.childNodes[0].getAttribute('value'));
  
  // increment the currentProgress value of the book
  if(currentProgress){
    currentProgress += amount;
  }else{
    currentProgress = amount;
  }
  // call AJAX processing 
  _currentBtnEvent = btnEvent;
  _currentBtnProgress = currentProgress;
  _currentBtnBookId = bookId;
  _mode = 'progress';
  processProgressUpdate(newlyStarted);
}

/* ----------------------------------------------------------------------------
// Function:    checkPageCount()
//
// Params:      
//
// Desc:        get pageCount & progress of event that called, return a true
//              or false value as to whether the amount to be added to progress
//              will exceed the PageCount.
//              
// Invocations: readingList.php 
//              --function processProgressUpdate(){
//                    if (checkPageCount()){...}}
// --------------------------------------------------------------------------*/
function checkPageCount(){
  let pageCount = parseInt(_currentBtnEvent.parentNode.parentNode.childNodes[1].getAttribute('max'));
  let progress = parseInt(_currentBtnEvent.parentNode.childNodes[0].value);
  let diff = Math.abs(_currentBtnProgress - progress);

//  console.log('(_currentBtnProgress - progress) + progress: ', ((_currentBtnProgress - progress) + progress));
  //console.log('diff: ', diff);
  if ( ((_currentBtnProgress - progress) + progress) > pageCount ){
    return false;
  } else{
    return true;
  }
}

// pageCountEvent is the button used to confirm the entry of the whole number in the input space
function updatePageCount(pageCountEvent){
  var standinContainer = pageCountEvent.parentNode.childNodes[2];
  var pageCountValue = pageCountEvent.parentNode.childNodes[2].value;  
  if(pageCountValue > 0){
    console.log('pageCount: ', pageCountEvent.value);
    var bookIdNode = pageCountEvent.parentNode.parentNode.parentNode.parentNode.childNodes[0].childNodes[0];
    var bookId = bookIdNode.getAttribute('book-id');
    
    _currentBtnEvent = standinContainer;

    console.log('standinContainer: ', standinContainer);
    // console.log('progressBarNode max: ', progressBarNode.max);

    // call AJAX processing
    _mode = 'pageCount';
    processPageCountUpdate(pageCountValue, bookId);
  }
}

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
// Function:    processFinishedToggle()      
//
// Params:      
// Desc:        ajax call to send Finished update to server
//              
// Invocations: readingList.js 
//              --
// --------------------------------------------------------------------------*/
function processFinishedToggle(id, finished){
    // check if XHR is busy or ready to connect
    if(xhr.readyState==0 || xhr.readyState == 4){
      // send that data to the server-side
      let urlString = './readingListAjax.php?mode=' + _mode + '&id=' + id + '&finished=' + finished;

      xhr.open('GET', urlString, true);
      
      // call this to set the DateFinished label below the Finished status button
      xhr.onreadystatechange = handleServerResponseFinishedToggle;
      
      xhr.send(null);
    }
    else{
      // wait a second and call itself again
      setTimeout('processFinishedToggle()', 1000);
    }
}

/* ----------------------------------------------------------------------------
// Function:    processPageCountUpdate()      
//
// Params:      
// Desc:        ajax call to send PageCount and Progress updates to server
//              
// Invocations: readingList.js 
//              --
// TODO:        re-format this to use parameters and not Global variables.
// --------------------------------------------------------------------------*/
function processPageCountUpdate(pageCount, id){
    // check if XHR is busy or ready to connect
    if(xhr.readyState==0 || xhr.readyState == 4){
      // send that data to the server-side
      let urlString = './readingListAjax.php?mode=' + _mode + '&pageCountValue=' + pageCount + '&progressValue=' + 0 + '&id=' + id;
      //console.log('URL: ', urlString);
      xhr.open('GET', urlString, true);
      
      xhr.onreadystatechange = handleServerResponsePageCount;
      
      xhr.send(null);
    }
    else{
      // wait a second and call itself again
      setTimeout('processPageCountUpdate()', 1000);
    }
}

/* ----------------------------------------------------------------------------
// Function:    processProgressUpdate(progressValue, id)      
//
// Params:      id - ID of the book
//              progressValue - progress value to update.  
//              --Its either an arbitrary value set by slider (may be eliminated)
//              --OR
//              --Its the current progress value incremented by +1, +5, +10 
//                depending on button pressed by user
//
// Desc:        ajax call to pass book.progress values to update to the server
//              
// Invocations: readingList.js 
//              --function incrementProgress(btnEvent, amount){...}
//              --function updateProgress(updateEvent){...}
//
// TODO:        re-format this to use parameters and not Global variables.
// --------------------------------------------------------------------------*/
function processProgressUpdate(started){
  // only execute the AJAX call if the increment to the Progress won't exceed page count
  if (checkPageCount()){
    // check if XHR is busy or ready to connect
    if(xhr.readyState==0 || xhr.readyState == 4){
      // send that data to the server-side
      let urlString = './readingListAjax.php?mode=' + _mode + '&progressValue=' + _currentBtnProgress + '&id=' + _currentBtnBookId + '&started=' + started;
      //console.log('URL: ', urlString);
      xhr.open('GET', urlString, true);
      
      xhr.onreadystatechange = handleServerResponseProgress;
      
      xhr.send(null);
    }
    else{
      // wait a second and call itself again
      setTimeout('processProgressUpdate()', 1000);
    }
  }
}

// Set the DateFinished label below the Finished status button
function handleServerResponseFinishedToggle(){
  if(this.readyState == 4){
    if(xhr.status == 200){
        msg = xhr.responseXML.documentElement.firstChild.data;
        console.log('finishedToggle resp: ', msg);

        // GET STATUS BUTTON's DataFinished value
        let statusButton = _currentBtnEvent.parentNode.childNodes[0].getAttribute('statusvalue');
        let dateFinishedLabel = _currentBtnEvent.parentNode.childNodes[1];
        
        // OPTIMIZE
        // this may be superfluous and I might just delete it later if I decide I don't want
        // two places displaying the same data.
        let dateFinishedAdv = _currentBtnEvent.parentNode.parentNode.parentNode.parentNode.parentNode.childNodes[1].childNodes[1].childNodes[2];

        if(msg !== 'null'){
          dateFinishedLabel.innerHTML = msg;
          dateFinishedAdv.innerHTML += msg;                 // OPTIMIZE
        }else{
          dateFinishedLabel.innerHTML = '';
          dateFinishedAdv.innerHTML = 'Date Finished:';     // OPTIMIZE
        }

        console.log('dateFinishedAdv: ', dateFinishedAdv);

        xhr.abort();
    }else{
        console.log('readyState: ', xhr.readyState);
        alert('Something went wrong');
    }
  }
}


function handleServerResponseProgress(){
  if(this.readyState == 4){
      if(xhr.status == 200){
          msg = xhr.responseXML.documentElement.firstChild.data;
          
          // GET PROGRESS OUTPUT element
          let progressOutput = _currentBtnEvent.parentNode.childNodes[0];
          let progressBar = _currentBtnEvent.parentNode.parentNode.childNodes[1];
          let dateStartedLabel = _currentBtnEvent.parentNode.childNodes[4];
          // console.log('dateStartedLabel: ', dateStartedLabel);
          
          // console.log('progressOutput node: ', progressOutput);
          //console.log('event progress Output value BUTTON ', progressOutput.getAttribute('value'));
          
          respArray = msg.split(',');
          progress = parseInt(respArray[0]);
          
          console.log('type of msg: ', progress);
          
          progressOutput.setAttribute('value', progress);
          progressOutput.value = progress;
          progressBar.value = progress;
          
          if (respArray.length > 1){
            dateStarted = respArray[1];
            let dateStartedNode = _currentBtnEvent.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode.childNodes[1].childNodes[1].childNodes[0];
            dateStartedNode.setAttribute('value', dateStarted);
            dateStartedNode.innerHTML += dateStarted;
            if(progress > 0){
              dateStartedLabel.innerHTML = 'Started: ' + dateStarted;
              dateStartedLabel.style.display = 'block';
              dateStartedLabel.setAttribute('value', dateStarted);
              dateStartedLabel.value = dateStarted;
            }
          }
          
          xhr.abort();
      }else{
          console.log('readyState: ', xhr.readyState);
          alert('Something went wrong');
      }
  }
}

function handleServerResponsePageCount(){
  if(this.readyState == 4){
      if(xhr.status == 200){

          // msg should be the newly updated PageCount, fetched from DB, not just pasting in
          // the user's value
          msg = xhr.responseXML.documentElement.firstChild.data;

          var progressContainerNode = _currentBtnEvent.parentNode.childNodes[1];
          var pageCountUpdateButton = _currentBtnEvent.parentNode.childNodes[3];

          console.log('pageCountUpdateButton: ', pageCountUpdateButton);
          let progressBarNode = progressContainerNode.childNodes[1];
          let progressPageCountNode = progressContainerNode.childNodes[2].childNodes[1];
          let progressOutputNode = progressContainerNode.childNodes[3].childNodes[0];

          progressBarNode.value = 0;
          // progressBarNode.setAttribute('value', 0);
          progressBarNode.max = msg;

          progressPageCountNode.value = msg;
          progressPageCountNode.setAttribute('value', msg);
          progressPageCountNode.innerHTML = msg;

          progressOutputNode.value = 0;
          progressOutputNode.setAttribute('value', 0);
          progressOutputNode.max = msg;
          
          progressContainerNode.style.display = 'block';
          _currentBtnEvent.style.display = 'none';
          pageCountUpdateButton.style.display = 'none';

          xhr.abort();
      }else{
          console.log('readyState: ', xhr.readyState);
          alert('Something went wrong');
      }
  }
}