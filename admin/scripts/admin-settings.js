/* global wpsp */
import addHtml from './add-html';
import debounce from './debounce';

export default {
    init() {
        this.settingsPage = $('.wpsp-admin-settings');
        if (this.settingsPage.length < 1) return;

        this.table = $('.wp-list-table');
        this.tabs = $('.tabs .tab');

        this.form = $('#add-update-connection');
        this.formName = this.form.find('input[name=name');
        this.formUrl = this.form.find('input[name=url');
        this.formKey = this.form.find('input[name=key');
        this.formConnection = this.form.find('input[name=connection-id');

        this.setup();
        this.bindEvents();
    },

    setup() {
        $('#add-connection-find-replace').createRepeater();
    },

    changeTabs($$) {
        this.tabs.removeClass('active');
        $$.addClass('active');
        $('.tabbed-content .content-section').removeClass('active');
        $('.tabbed-content .content-section[data-tab="' + $$.data('tab-target') + '"]').addClass('active');
    },

    ajaxUpdateSetting(setting, value) {
        $.ajax({
            type: 'post',
            dataType: 'json',
            url: wpsp.ajaxUrl,
            data: {
                action: 'wpsp_update_setting',
                nonce: $('#wpsp-update-settings').val(),
                setting: setting,
                value: value,
            },
            success: (response) => {
                console.log(response);
            },
            error: (response) => {
                console.log(response);
            },
        });
    },

    ajaxResetKey() {
        $.ajax({
            type: 'post',
            dataType: 'json',
            url: wpsp.ajaxUrl,
            data: {action: 'wpsp_reset_key', nonce: $('#wpsp-reset-key-nonce').val()},
            success: (response) => {
                if (response.success)
                    $('input[name=wpsp-key]').val(response.data);
            },
            error: (response) => {
                console.log(response);
            },
        });
    },

    ajaxAddUpdateConnection($$) {
        $.ajax({
            type: 'post',
            dataType: 'json',
            url: wpsp.ajaxUrl,
            data: {action: 'wpsp_add_update_connection', form: $$.serialize(), nonce: $('#wpsp-connection-nonce').val()},
            success: (response) => {
                if (response.success) {
                    this.updateConnections(response.data);
                    this.resetForm();
                }

            },
            error: (response) => {
                console.log(response);
            },
        });
    },

    updateConnections(connections) {
        let body = this.table.find('tbody');
        body.empty();
        connections.forEach((connection) => {
            body.append(addHtml.connectionRow(connection));
        });
    },

    setupEditConnection(connectionData) {
        this.formName.val(connectionData.name);
        this.formUrl.val(connectionData.url);
        this.formKey.val(connectionData.key);
        this.formConnection.val(connectionData.ID);

        let inputs = $('#add-connection-find-replace .inputs');
        inputs.empty();
        connectionData['find_replace'].forEach((array, index) => {
            inputs.append(addHtml.findReplaceInputs(array, index));
        });

        this.form.find('.connection-clear').removeClass('hidden');
        let title = $('.connection-form-title');
        title.text(title.data('update'));
        $('#add-update-connection-submit').prop('disabled', false);
    },

    resetForm() {
        let form = $('#add-update-connection');
        form.find('input[type=text], textarea, input[type=hidden]').val('');
        form.find('.connection-clear').addClass('hidden');
        $('#add-connection-find-replace .inputs').empty();

        $('#add-update-connection-submit').prop('disabled', true);
    },

    ajaxDeleteConnection(connectionId) {
        $.ajax({
            type: 'post',
            dataType: 'json',
            url: wpsp.ajaxUrl,
            data: {action: 'wpsp_delete_connection', connection_id: connectionId, nonce: $('#wpsp-connection-nonce').val()},
            success: (response) => {
                if (response.success) {
                    this.updateConnections(response.data);
                    this.resetForm();
                }
            },
            error: (response) => {
                console.log(response);
            },
        });
    },

    ajaxCheckUrl() {
        let url = this.formUrl.val();
        let key = this.formKey.val();

        if (!url || !key) return;
        $.ajax({
            type: 'post',
            dataType: 'json',
            url: wpsp.ajaxUrl,
            data: {action: 'wpsp_check_remote_url', url: url, key: key, nonce: $('#wpsp-connection-nonce').val()},
            success: (response) => {
                if (response.success) {
                    this.setUrlMatch(response.data);
                } else {
                    this.showUrlMatchError();
                }
            },
            error: (response) => {
                console.log(response);
                this.showUrlMatchError();
            },
        });
    },

    setUrlMatch(url) {
        let localUrl = window.location.hostname;
        if (window.location.port) localUrl += ':' + window.location.port;
        let remoteUrl = url.replace(/(^\w+:|^)\/\//, '').replace(/\/$/, '');
        let findReplace = [
            ['//' + localUrl, '//' + remoteUrl],
        ];
        let inputs = $('#add-connection-find-replace .inputs');
        inputs.empty();
        findReplace.forEach((array, index) => {
            inputs.append(addHtml.findReplaceInputs(array, index));
        });

        $('#add-update-connection-submit').prop('disabled', false);
    },

    showUrlMatchError() {
        console.log('match error');
        let inputs = $('#add-connection-find-replace .inputs');
        inputs.empty();
        $('#add-update-connection-submit').prop('disabled', true);
    },

    bindEvents() {
        let _this = this;
        $('#connection-clear').click(function (e) {
            e.preventDefault();
            _this.resetForm();
        });

        this.tabs.click(function () {
            _this.changeTabs($(this));
        });

        $('#add-update-connection').submit(function (e) {
            e.preventDefault();
            _this.ajaxAddUpdateConnection($(this));
        });

        this.table.on('click', '.connection-edit', function () {
            _this.setupEditConnection($(this).data('connection'));
        });

        this.table.on('click', '.connection-delete', function () {
            _this.ajaxDeleteConnection($(this).data('connection'));
        });

        $('#wpsp-allow-pull').change(function () {
            _this.ajaxUpdateSetting('wpsp_allow_pull', $(this).is(':checked'));
        });
        $('#wpsp-allow-push').change(function () {
            _this.ajaxUpdateSetting('wpsp_allow_push', $(this).is(':checked'));
        });

        $('#reset-key').click(function () {
            _this.ajaxResetKey();
        });


        const ajaxCheckUrl = debounce(function () {
            _this.ajaxCheckUrl();
        }, 700);

        this.formUrl.on('input', ajaxCheckUrl);
        this.formKey.on('input', ajaxCheckUrl);

    },
};