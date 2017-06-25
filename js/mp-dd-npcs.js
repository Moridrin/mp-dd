/**
 * Created by moridrin on 4-6-17.
 */

var scripts = document.getElementsByTagName("script");
var pluginBaseURL = scripts[scripts.length - 1].src.split('/').slice(0, -3).join('/');

function mp_dd_add_family_link(endNode, linkID, npcID, npcName, linkType) {
    endNode = document.getElementById(endNode);

    var linkTR = document.createElement("tr");
    linkTR.setAttribute("id", "npc_link_" + linkID);
    var linkLabelTD = document.createElement("td");
    var linkNameTD = document.createElement("td");
    var deleteTD = document.createElement("td");

    var linkIDs = document.createElement("input");
    linkIDs.setAttribute("type", "hidden");
    linkIDs.setAttribute("name", "link_ids[]");
    linkIDs.setAttribute("value", linkID);
    linkLabelTD.appendChild(linkIDs);
    var linkTypeHidden = document.createElement("input");
    linkTypeHidden.setAttribute("type", "hidden");
    linkTypeHidden.setAttribute("name", "link_" + linkID + "_link_type");
    linkTypeHidden.setAttribute("value", linkType);
    linkLabelTD.appendChild(linkTypeHidden);
    var linkLabel = document.createElement("label");
    linkLabel.innerHTML = linkType === "0" ? "Spouse" : "Child";
    linkLabelTD.appendChild(linkLabel);

    var linkNPCID = document.createElement("input");
    linkNPCID.setAttribute("type", "hidden");
    linkNPCID.setAttribute("name", "link_" + linkID + "_npc_id");
    linkNPCID.setAttribute("value", npcID);
    linkNameTD.appendChild(linkNPCID);
    var linkName = document.createElement("label");
    linkName.innerHTML = npcName;
    linkNameTD.appendChild(linkName);

    var deleteButton = document.createElement("img");
    deleteButton.setAttribute("src", pluginBaseURL + "/images/icon-delete.svg");
    deleteButton.setAttribute("style", "height: 20px; margin-left: 5px;");
    deleteButton.setAttribute("onclick", "deleteRow('" + linkID + "')");
    deleteTD.appendChild(deleteButton);

    linkTR.appendChild(linkLabelTD);
    linkTR.appendChild(linkNameTD);
    linkTR.appendChild(deleteTD);

    endNode.parentNode.insertBefore(linkTR, endNode);
}

//noinspection JSUnusedGlobalSymbols
function deleteRow(linkID) {
    var tr = document.getElementById("npc_link_" + linkID);
    removeField(tr);
    event.preventDefault();
}

function removeField(field) {
    if (field !== null) {
        field.parentElement.removeChild(field);
    }
}
