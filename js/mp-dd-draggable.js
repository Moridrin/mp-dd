var offset_data; //Global variable as Chrome doesn't allow access to event.dataTransfer in dragover

function drag_start(event) {
    var style = window.getComputedStyle(event.target, null);
    offset_data = (parseInt(style.getPropertyValue("left"), 10) - event.clientX) + ',' + (parseInt(style.getPropertyValue("top"), 10) - event.clientY);
    event.dataTransfer.setData("text/plain", offset_data);
}
function drag_over(event) {
    var offset;
    try {
        offset = event.dataTransfer.getData("text/plain").split(',');
    }
    catch (e) {
        offset = offset_data.split(',');
    }
    var dm = document.getElementById('dragme');
    dm.style.left = (event.clientX + parseInt(offset[0], 10)) + 'px';
    dm.style.top = (event.clientY + parseInt(offset[1], 10)) + 'px';
    event.preventDefault();
    return false;
}
function drop(event) {
    var offset;
    try {
        offset = event.dataTransfer.getData("text/plain").split(',');
    }
    catch (e) {
        offset = offset_data.split(',');
    }
    var dm = document.getElementById('dragme');
    dm.style.left = (event.clientX + parseInt(offset[0], 10)) + 'px';
    dm.style.top = (event.clientY + parseInt(offset[1], 10)) + 'px';
    event.preventDefault();
    return false;
}
window.onload = function () {
    var dm = document.getElementById('dragme');
    dm.addEventListener('dragstart', drag_start, false);
    document.body.addEventListener('dragover', drag_over, false);
    document.body.addEventListener('drop', drop, false);
};