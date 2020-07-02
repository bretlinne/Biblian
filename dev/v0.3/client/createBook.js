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