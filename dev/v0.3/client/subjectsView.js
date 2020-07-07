// ============================================================================
// readingList.js
//
//
//
// search 'OPTIMIZE'; 'TODO'
// ============================================================================
_mode = null;
_currentBtnEvent = null;
// ============================================================
// SHOW NOTIFICATION
//
// Look for GET from the ClearReload.php page and INJECT 
// the message and class type into the notificaiton
// DELETE LATER - I don't think this is needed as I'm probably not going to use any form-submits
//   on this page, just AJAX
// ============================================================
// const queryString = window.location.search;
// const urlParams = new URLSearchParams(queryString);

// if (urlParams.get('bouncedMsg')){
//   let notification = document.getElementById('notification');
//   notification.style.display = 'block';
//   notification.innerHTML = urlParams.get('bouncedMsg');
//   if(urlParams.get('bouncedType') === 'success'){
//     notification.setAttribute('class', 'alert alert-success');
//   }else{
//     notification.setAttribute('class', 'alert alert-danger');
//   }
// }

/* ----------------------------------------------------------------------------
// Function:    updateSubject(e)
//
// Params:      e - event of UI element used to call this function
//
// Desc:        Ajax call to send updates to server on Subject Name
//              In the HTML, there are 3 relevent attributes:
//              --data-id={$SubjectID}          - id of DB entry
//              --master-name={$SubjectName}    - master copy of the last accepted name in DB
//              --value={$SubjectName}          - editable name
//                --once accepted, the edited value replaces the master-name
//              
// Invocations: subjectsView.php 
//              --echo "<input type='number' class='progress-output' 
//                  onkeyup='updateProgress(this)' min=0 max={$PageCount} 
//                  value={$Progress}></input>";
// --------------------------------------------------------------------------*/
function updateSubject(e){
    e.style.color = 'MediumSeaGreen';
    e.style.fontStyle = 'italic';
    e.style.fontWeight = 'bold';
    masterName = e.getAttribute('master-name');

    // IF USER DELETES ALL CHARACTERS PROMPT IF THEY WANT TO DELETE THE SUBJECT
    // IF YES -- DELETE
    // IF NO -- PASTE THE ORIGINAL NAME, UNDOING ANY EDITING THE USER ATTEMPTED
    //          IN OTHER WORDS, DO NOT ALLOW AN UPDATE OF EMPTY STRING '' TO DB

    if(e.value === ''){
        // TODO
        if (confirm('Empty string; delete from Biblian?')){
            deleteSubject(e.parentNode.childNodes[1]);
        }
        // REPLACE '' EMPTY STRING WITH MASTER VALUE &
        // CALL UPDATE, PASSING IN MASTER VALUE
        else{
            e.setAttribute('value', masterName);
            e.value = masterName;
            updateSubject(e);
        }
    }else{
        // AJAX CALL TO UPDATE THE DB ENTRY
        _currentBtnEvent = e;
        _mode = 'update';
        processSubjectUpdate(e.getAttribute('data-id'), e.value);
    }
}

function focusOnInput(e){
    e.style.color = 'MediumSeaGreen';
    e.style.fontStyle = 'italic';
    e.style.fontWeight = 'bold';
}

function loseFocusInput(e){
    e.style.color = 'black';
    e.style.fontStyle = 'normal';
    e.style.fontWeight = 'normal';
}

function deleteSubject(e){
    let id = e.parentNode.childNodes[0].getAttribute('data-id');
    
    _currentBtnEvent = e;
    _mode = 'delete';

    processSubjectDelete(id);
}

function createSubject(e){
    let name = capitalizeFirstLetter(e.parentNode.childNodes[1].value);
    let subjectList = document.getElementById('subjectList').getAttribute('data-value');
    let found = false;

    console.log('called');
    subjectList = subjectList.split(',');
    for (let i = 0; i < subjectList.length; i++) {
        if(name === subjectList[i]){
            found = true;
            break;
        }
    }
    if(found){
        let notificaiton = document.getElementById('notification');
            notification.style.display = 'block';
            notificaiton.style.opacity = '1'; 
            notification.style.position = 'fixed';
            notification.style.zIndex = '1000';
            notification.style.width = '80.2%';
            notification.style.top = '10px';
            notification.innerHTML = 'That Subject already exists';
            notification.setAttribute('class', 'alert alert-danger');
            setTimeout(function(){fadeOut(notificaiton, 5);}, 1500);
    }else{
        _currentBtnEvent = e;
        _mode = 'create';
        processSubjectCreate(name);
    }
}

function capitalizeFirstLetter(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
}
// ----------------------------------------------------------------------------
//
// AJAX 
//
// ----------------------------------------------------------------------------
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
// Function:    processSubjectUpdate(id, name)      
//
// Params:      id - id of subject entry to update
//              name - new name to update
// Desc:        ajax call to send SubjectName to server
//              
// Invocations: subjectsView.js 
//              --
// --------------------------------------------------------------------------*/
function processSubjectUpdate(id, name){
    // check if XHR is busy or ready to connect
    if(xhr.readyState==0 || xhr.readyState == 4){
      // send that data to the server-side
      let urlString = './subjectsViewAjax.php?mode=' + _mode + '&id=' + id + '&name=' + name;

      xhr.open('GET', urlString, true);
      
      // call this to set the DateFinished label below the Finished status button
      xhr.onreadystatechange = handleServerResponseSubjectUpdate;
      
      xhr.send(null);
    }
    else{
      // wait a second and call itself again
      setTimeout('processSubjectUpdate()', 1000);
    }
}

/* ----------------------------------------------------------------------------
// Function:    processSubjectDelete(id)      
//
// Params:      id - id of subject entry to delete
//
// Desc:        ajax call to delete subject from DB
//              
// Invocations: subjectsView.js 
//              --
// --------------------------------------------------------------------------*/
function processSubjectDelete(id){
    if(xhr.readyState==0 || xhr.readyState == 4){
        // send that data to the server-side
        let urlString = './subjectsViewAjax.php?mode=' + _mode + '&id=' + id;
  
        xhr.open('GET', urlString, true);
        
        // call this to set the DateFinished label below the Finished status button
        xhr.onreadystatechange = handleServerResponseSubjectDelete;
        
        xhr.send(null);
      }
      else{
        // wait a second and call itself again
        setTimeout('processSubjectDelete()', 1000);
      }
}

function processSubjectCreate(name){
    if(xhr.readyState==0 || xhr.readyState == 4){
        let urlString = './subjectsViewAjax.php?mode=' + _mode + '&name=' + name;
  
        xhr.open('GET', urlString, true);
        
        // call this to set the DateFinished label below the Finished status button
        xhr.onreadystatechange = handleServerResponseSubjectCreate;
        
        xhr.send(null);
      }
      else{
        // wait a second and call itself again
        setTimeout('processSubjectCreate()', 1000);
      }
}

function handleServerResponseSubjectUpdate(){
    if(this.readyState == 4){
        if(xhr.status == 200){
            // msg should be the newly updated name, fetched from DB, not just pasting in
            // the user's value
            msg = xhr.responseXML.documentElement.firstChild.data;
  
            e = _currentBtnEvent;
            e.value = msg;
            // e.setAttribute('master-name', msg);
            xhr.abort();
        }else{
            console.log('readyState: ', xhr.readyState);
            alert('Something went wrong');
        }
    }
}


// calls fadeOut(), which is defined in ./client/res/inc/fade.js
function handleServerResponseSubjectDelete(){
    if(this.readyState == 4){
        if(xhr.status == 200){
            // msg should be the newly updated name, fetched from DB, not just pasting in
            // the user's value
            msg = xhr.responseXML.documentElement.firstChild.data;

            let headReadContainer = document.getElementById('headReadContainer');
            let totalRows = headReadContainer.getAttribute('row-count');

            let notificaiton = document.getElementById('notification');
            notification.style.display = 'block';
            notificaiton.style.opacity = '1'; 
            notification.style.position = 'fixed';
            notification.style.zIndex = '1000';
            notification.style.width = '80.2%';
            notification.style.top = '10px';
                
            if(msg === 'successBoth'){
                let removeTarget = _currentBtnEvent.parentNode; // the whole container for the subject
            
                // Fade Out and Remove the element
                removeTarget.style.opacity = '0';
                setTimeout(function(){removeTarget.parentNode.removeChild(removeTarget);}, 750);                    
                
                notification.innerHTML = '<p>Subject deleted & Removed subject from associated books.</p>';
                notification.setAttribute('class', 'alert alert-success');
                setTimeout(function(){fadeOut(notificaiton, 5);}, 1500); 
                headReadContainer.setAttribute('row-count', (parseInt(totalRows) - 1));
            }else if(msg === 'successSubjectOnly'){
                let removeTarget = _currentBtnEvent.parentNode; // the whole container for the subject
            
                // Fade Out and Remove the element
                removeTarget.style.opacity = '0';
                setTimeout(function(){removeTarget.parentNode.removeChild(removeTarget);}, 750);                    
                
                notification.innerHTML = 'Subject deleted.';
                notification.setAttribute('class', 'alert alert-success');
                setTimeout(function(){fadeOut(notificaiton, 5);}, 1500); 
                headReadContainer.setAttribute('row-count', (parseInt(totalRows) - 1));
            }else{
                notification.innerHTML = 'DB Operation Failed';
                notification.setAttribute('class', 'alert alert-danger');
                setTimeout(function(){fadeOut(notificaiton, 5);}, 1500);  
            }
            console.log('total rows: ', headReadContainer.getAttribute('row-count'));
            xhr.abort();
        }else{
            console.log('readyState: ', xhr.readyState);
            alert('Something went wrong');
        }
    }
}

/*-----------------------------------------------------------------------------
// Function:    handleServerResponseSubjectCreate()      
//
// Params:      uses global variables: _currentBtnEvent
//              XHR response:
//              - comma-separated msg from server that contains 
//                   " 'name', 'lastInsertedID' "
//              
// Desc:        AJAX functions. Handles response from server for AJAX.
//              just reloads the page.  Its far simpler
//             
// Invocations: readingList.php 
//              --function processSubjectCreate(name){
//                ...
//                xhr.onreadystatechange = handleServerResponseSubjectCreate;
//                ... }
//---------------------------------------------------------------------------*/
function handleServerResponseSubjectCreate(){
    if(this.readyState == 4){
        if(xhr.status == 200){
            // msg should be the newly updated name, fetched from DB, not just pasting in
            // the user's value
            msg = xhr.responseXML.documentElement.firstChild.data;

            // // get the total row count, which is embedded in headReadContainer as row-count
            // let totalRows = document.getElementById('headReadContainer').getAttribute('row-count');
            // let headReadContainer = document.getElementById('headReadContainer');

            // let respArray = msg.split(',');
            // let name = respArray[0];
            // let id = parseInt(respArray[1]);
            
            // let subjectList = document.getElementById('subjectList').getAttribute('data-value');
            
            // subjectList = subjectList.split(',');

            // // create a new subject header and append to the list
            // // 1 get the container for the subjects
            // let leftContainer = document.getElementById('readContainerLeft');
            // let rightContainer = document.getElementById('readContainerRight');

            // // 2 create a new subject head container
            // let newSubject = document.createElement('DIV');
            // newSubject.setAttribute('class', 'headContainerSubject');
            
            // // 3 create a new input
            // let newInput = document.createElement('INPUT');
            // newInput.setAttribute('type', 'text'); 
            // newInput.setAttribute('class', 'containerSubject');
            // newInput.setAttribute('oninput', 'updateSubject(this)');
            // newInput.setAttribute('onfocus', 'focusOnInput(this)');
            // newInput.setAttribute('onfocusout', 'loseFocusInput(this)');
            // newInput.setAttribute('master-name', name);
            // newInput.setAttribute('data-id', id);
            // newInput.setAttribute('value', name);
            
            // // 4 create a new delete button
            // let newButton = document.createElement('BUTTON');
            // newButton.setAttribute('class', 'btn btn-primary buttonDelete');
            // newButton.setAttribute('onclick', 'deleteSubject(this)');
            
            // // 5 create a new span to append to delete button
            // let newSpan = document.createElement('SPAN');
            // newSpan.setAttribute('class', 'glyphicon glyphicon-trash');

            // newButton.appendChild(newSpan);
            // newSubject.appendChild(newInput);
            // newSubject.appendChild(newButton);

            // // N decide where the new subejct is to be inserted alphabetically and place it in left or right column            

            // // capitalize the first letter of the entry
            // subjectList.push(name);
            // subjectList.sort();
            // console.log(subjectList);
            // for (let i = 0; i < subjectList.length; i++) {
            //     if(subjectList[i] === name){
            //         console.log('i: ', i, '; ', subjectList[i]);
            //         console.log('Math.floor(totalRows / 2): ', Math.floor(totalRows / 2));
            //         console.log('totalRows: ', totalRows);
                    
            //         // if value is at math.floor(total/2), then check the alphabetical value
            //         // of the word and either put on right or left
            //         if((i === Math.floor(totalRows / 2)) || (i === Math.floor(totalRows / 2) - 1) ){
            //             // if first letter is same
                        
            //             indexAdj = (i - (Math.ceil(totalRows / 2))) + 1;
                        
            //             // if(name[0] === subjectList[i][0]){
            //             //     console.log('[0] === ');
            //             //     if(name[1] > subjectList[i][1]){
            //             //         console.log('[1] greater');
            //             //         rightContainer.insertBefore(newSubject, rightContainer.childNodes[indexAdj]);         
            //             //     }else if(name[1] === subjectList[i][1]){
            //             //         console.log('[1] ===; ', name[1], '; ', subjectList[i][1]);
            //             //         if(name[2] > subjectList[i][2]){
            //             //             console.log('[2] greater');
            //             //             rightContainer.insertBefore(newSubject, rightContainer.childNodes[indexAdj]);   
            //             //         }else if(name[2] === subjectList[i][2]){
            //             //             console.log('[2] ===; ', name[2], '; ', subjectList[i][2]);
            //             //             if(name[3] > subjectList[i][3]){
            //             //                 console.log('[3] greater; indexAdj: ', indexAdj, '; i: ', i, '; Math.ceil(totalRows / 2): ', Math.ceil(totalRows / 2));
            //             //                 rightContainer.insertBefore(newSubject, rightContainer.childNodes[indexAdj]);   
            //             //             }else if(name[3] === subjectList[i][3]){
            //             //                 console.log('[3] ===; ', name[3], '; ', subjectList[i][3]);
            //             //                 if(name[4] >= subjectList[i][4]){
            //             //                     rightContainer.insertBefore(newSubject, rightContainer.childNodes[indexAdj]);   
            //             //                 }else{
            //             //                     leftContainer.insertBefore(newSubject, leftContainer.childNodes[i]); 
            //             //                 }
            //             //             }
            //             //         }
            //             //     }
            //             // }
            //         }
            //         else if(i < Math.floor(totalRows / 2)){
            //             leftContainer.insertBefore(newSubject, leftContainer.childNodes[i]); 
            //         }else{
            //             indexAdj = i - (Math.ceil(totalRows / 2));
            //             rightContainer.insertBefore(newSubject, rightContainer.childNodes[indexAdj]); 
            //         }
            //     }
            // }
            // headReadContainer.setAttribute('row-count', (parseInt(totalRows) + 1));
            if(msg){
                location.reload();
            }else{
                alert('Something went wrong');
            }
            
            xhr.abort();
        }else{
            console.log('readyState: ', xhr.readyState);
            alert('Something went wrong');
        }
    }   
}