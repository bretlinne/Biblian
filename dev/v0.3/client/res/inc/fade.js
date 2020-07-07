function fadeIn(id, spd){
    if(id.style.opacity == ''){
        id.style.opacity = 1;
        id.style.filter = 'alpha(opacity=' + 100 + ')';
    }
    if(id.style.opacity < 1){
        let opac = 0;
        let cycle = setInterval(increaseOpacity, spd);
        function increaseOpacity(){
            opac += 0.01;
            if(opac >= 1){
                id.style.opacity = 1;
                opac = 1;
                clearInterval(cycle);
            }
            id.style.opacity = opac;
            id.style.filter = "alpha(opacity=" + (opac * 100) + ")"; // IE fallback
        }
    }else{
        clearInterval(cycle);
    }
}

function fadeOut(id,spd){
    if(id.style.opacity == ""){
        id.style.opacity = 1;
        id.style.filter = "alpha(opacity=" + 100 + ")";
    }
    if(id.style.opacity > 0){
        var opac = 1;
        var cycle = setInterval(decreaseOpacity,spd);
        function decreaseOpacity() {
            opac -= 0.01;
            if(opac <= 0){
                id.style.opacity = 0;
                opac = 0;
                clearInterval(cycle);
            }
            id.style.opacity = opac;
            id.style.filter = "alpha(opacity=" + (opac * 100) + ")";

        }
    } else {
        clearInterval(cycle);
    }
}