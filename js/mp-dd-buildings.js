/**
 * Created by moridrin on 4-6-17.
 */

function mp_dd_add_building(fieldID, fieldHTML, show) {
    var container = document.getElementById("buildings-placeholder");

    var buildingDiv = document.createElement("div");
    buildingDiv.setAttribute("id", fieldID);

    var button = document.createElement("button");
    button.setAttribute("id", "building_header_" + fieldID);
    button.setAttribute("type", "button");
    button.setAttribute("class", "accordion");
    button.innerHTML = "Building " + fieldID;
    button.setAttribute("onclick", "mp_dd_set_accordion_click_event()");
    buildingDiv.appendChild(button);

    var panelDiv = document.createElement("div");
    panelDiv.setAttribute("class", "panel");

    var buildingFieldsTable = document.createElement("table");
    buildingFieldsTable.setAttribute("style", "width: 100%");
    var buildingFieldsRow = document.createElement("tr");
    buildingFieldsRow.appendChild(getBuildingHTML(fieldID, fieldHTML));
    buildingFieldsTable.appendChild(buildingFieldsRow);

    panelDiv.appendChild(buildingFieldsTable);
    buildingDiv.appendChild(panelDiv);
    container.appendChild(buildingDiv);

    if (show) {
        button.click();
    }
}

//noinspection JSUnusedGlobalSymbols
function mp_dd_set_accordion_click_event() {
    event.preventDefault();
    event.srcElement.classList.toggle("active");
    console.log(event.srcElement.classList.contains('active'));
    var panel = event.srcElement.nextElementSibling;
    if (event.srcElement.classList.contains('active')) {
        panel.setAttribute("style", "display: block;")
    } else {
        panel.setAttribute("style", "display: none;")
    }
}

//noinspection JSUnusedGlobalSymbols
function getBuildingHTML(fieldID, value) {
    var buildingHTML = document.createElement("textarea");
    buildingHTML.setAttribute("id", fieldID + "_html");
    buildingHTML.setAttribute("name", "building_" + fieldID + "_html");
    buildingHTML.setAttribute("style", "width: 100%; height: 150px;");
    if (value) {
        buildingHTML.innerHTML = value;
    }
    var buildingHTMLLabel = document.createElement("label");
    buildingHTMLLabel.setAttribute("style", "white-space: nowrap;");
    buildingHTMLLabel.setAttribute("for", fieldID + "_html");
    buildingHTMLLabel.innerHTML = "HTML";
    var buildingHTMLTD = document.createElement("td");
    buildingHTMLTD.setAttribute("id", fieldID + "_html_td");
    buildingHTMLTD.appendChild(buildingHTMLLabel);
    buildingHTMLTD.appendChild(getBR());
    buildingHTMLTD.appendChild(buildingHTML);
    return buildingHTMLTD;
}

function getBR() {
    var br = document.createElement("div");
    br.innerHTML = '<br/>';
    return br.childNodes[0];
}
