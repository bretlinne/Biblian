// ============================================================================
//
// Collapsible Details Section
//
// Callable Functions:
// --slideToggle(this, 200)
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
  
  var slideToggleTargeted = (adv, duration = 500) => {
    if (window.getComputedStyle(adv).display === 'none') {
      return slideDown(adv, duration);
    } else {
      return slideUp(adv, duration);
    }
  }
  
//   function toggleDetails(e){
//     var adv = e.children[1];
    
//     if(window.getComputedStyle(adv).display === 'block'){
//         hide(adv);      // simple implementation
//     }else{
//         show(adv);      // simple implementation
//     }
//   }