export default {
    findReplaceInputs(valArray, index) {
        return `<div class="items" data-index="${index}">
                <div class="item-content">
                <label>
                <span>Find</span>
                <input type="text" id="find-replace_${index}_find" placeholder="Find" data-name="find" name="find-replace[${index}][find]" value="${valArray[0]}">
                </label>
                <label>
                <span>Replace</span>
                <input type="text" id="find-replace_${index}_replace" placeholder="Replace" data-name="replace" name="find-replace[${index}][replace]" value="${valArray[1]}">
                </label>
                </div>
                <div class="pull-right repeater-remove-btn">
                <button class="btn btn-danger remove-btn">Remove</button>
                </div>
                <div></div></div>`;
    },

    connectionRow(connection) {
        let jsonData = this.escapeHtml(JSON.stringify(connection));
        return `<tr>
            <td class="option_id column-option_id has-row-actions column-primary" data-colname="ID">${connection.ID}</td>
            <td class="option_name column-option_name" data-colname="Name">${connection.name}</td>
            <td class="option_value column-option_value" data-colname="Value">${connection.url}</td>
            <td class="autoload column-autoload" data-colname="Options">
            <span class="connection-edit" data-connection="${jsonData}">Edit</span> /
            <span class="connection-delete" data-connection="${connection.ID}">Delete</span></td>
            </tr>`;
    },

    escapeHtml(text) {
        let map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            '\'': '&#039;',
        };

        return text.replace(/[&<>"']/g, function (m) {
            return map[m];
        });
    },

};