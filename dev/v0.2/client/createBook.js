// DEALS WITH THE SUBJECT DROP-DOWN MENU-->
function toggleField(hideObj,showObj){
    hideObj.disabled=true;        
    hideObj.style.display='none';
    showObj.disabled=false;   
    showObj.style.display='inline';
    showObj.focus();
}

//toggle the advanced section
$("#advanced-toggle").click(function() {
      $("#advanced").closest("tbody").toggle('fast');
});           

//deal with toggling the Currently Reading section
$("#reading-box").change(function() {
    if($(this).prop('checked')) {
        $('tr.start-reading').nextUntil('tr.stop-reading').toggle('fast');
    } else {
        $('tr.start-reading').nextUntil('tr.stop-reading').toggle('fast');
    }
});

/*-----------------------------------------------------------------------------
// Function:    (auto-executing function)()        
//
// Desc:        listener for the Progress input.  It should only be displayed 
//              if there's a non-0 value in the PageCount box
// Invocations: createBook.php
//---------------------------------------------------------------------------*/
(function (){
    var pageCount = document.getElementById('PageCount');
    pageCount.addEventListener('keyup', displayProgressField);
    //console.log(progressField.nodeValue);
})();

function displayProgressField(){
    var progress = document.getElementById('progress');
    var progressPlaceholder = document.getElementById('progress-placeholder');
    if(this.value > 0){
        progress.style.display = 'block';    
        progressPlaceholder.style.display = 'none';
    }else{
        progress.style.display = 'none';    
        progressPlaceholder.style.display = 'block';
    }

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
/*
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
*/