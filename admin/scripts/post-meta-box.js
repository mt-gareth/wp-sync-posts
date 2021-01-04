/* global wpsp */

export default {
    init() {
        this.metaBox = $('#wpsp-post-sync-form');
        if (this.metaBox.length < 1) return;

        this.formWrapper = $('.wpsp-form');
        this.confirmationWrapper = $('.wpsp-confirmation');

        this.connectionInput = $('#wpsp-connection');
        this.remotePostSelectionInput = $('#wpsp-post-selection');
        this.manualPostInput = $('#wpsp-new-post');
        this.localPostInput = $('#wpsp-local-post-id');

        this.pushButton = $('#wpsp-submit-push');
        this.pullButton = $('#wpsp-submit-pull');
        this.confirmButton = $('#wpsp-submit-confirm');
        this.cancelButton = $('#wpsp-submit-cancel');
        this.errorMessage = $('#wpsp-error-message');
        this.successMessage = $('#wpsp-success-message');
        this.data = {};

        this.updateManualPostInput();
        this.bindEvents();
    },

    updateManualPostInput() {
        if (this.remotePostSelectionInput.val() === 'manual') {
            this.manualPostInput.parents('.input-group').removeClass('hidden');
        } else {
            this.manualPostInput.parents('.input-group').addClass('hidden');
        }
    },

    updatePullButton() {
        if (this.remotePostSelectionInput.val() === 'new') {
            this.pullButton.addClass('hidden');
        } else {
            this.pullButton.removeClass('hidden');
        }
    },

    submit(direction) {
        this.data = {
            connection_id: this.connectionInput.val(),
            remote_post_selection: this.remotePostSelectionInput.val(),
            manual_post_id: this.manualPostInput.val(),
            local_post_id: this.localPostInput.val(),
            direction: direction,
        };


        this.showConfirmation();
    },

    showConfirmation() {
        this.successMessage.addClass('hidden');
        this.errorMessage.addClass('hidden');

        this.formWrapper.addClass('hidden');
        this.confirmationWrapper.removeClass('hidden');
        let pullMessage = $('.wpsp-confirmation-message-pull');
        let pushMessage = $('.wpsp-confirmation-message-push');
        if (this.data.direction === 'pull') {
            pullMessage.removeClass('hidden');
            pushMessage.addClass('hidden');
        } else {
            pullMessage.addClass('hidden');
            pushMessage.removeClass('hidden');
        }
    },

    hideConfirmation() {
        this.formWrapper.removeClass('hidden');
        this.confirmationWrapper.addClass('hidden');
    },

    ajaxSendRequest() {
        $.ajax({
            type: 'post',
            dataType: 'json',
            url: wpsp.ajaxUrl,
            data: {action: 'wpsp_start_sync', data: this.data, nonce: $('#wpsp-sync-nonce').val()},
            success: (response) => {
                if (response.success) {
                    this.showSyncSuccess()
                } else {
                    this.showSyncError(response.data);
                }
            },
            error: (response) => {
                this.showSyncError(response);
            },
        });
    },

    showSyncSuccess() {
        this.hideConfirmation();
        if(this.data.direction === 'push') {
            this.successMessage.removeClass('hidden');
        } else {
            //todo notify the user that the page pull worked
            window.location.reload();
        }
    },

    showSyncError(error) {
        this.hideConfirmation();
        this.errorMessage.removeClass('hidden');
        this.errorMessage.text(String(error));
        console.log(error);
    },

    bindEvents() {
        this.remotePostSelectionInput.change(() => {
            this.updateManualPostInput();
            this.updatePullButton();
        });

        this.pushButton.click((e) => {
            e.preventDefault();
            this.submit('push');
        });
        this.pullButton.click((e) => {
            e.preventDefault();
            this.submit('pull');
        });

        this.cancelButton.click((e) => {
            e.preventDefault();
            this.hideConfirmation();
        });

        this.confirmButton.click((e) => {
            e.preventDefault();
            this.ajaxSendRequest();
        });
    },
};