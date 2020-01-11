import '../css/app.scss';
import $ from 'jquery';
import Vue from 'vue'
import BootstrapVue from 'bootstrap-vue'

Vue.use(BootstrapVue);

new Vue({
    el: "#app",
    delimiters: ['{', '}'],
    data: {
        form: {
            email: '',
            text: '',
        },
        comments: [],
        error: false,
        errorMessage: '',
        success: false,
        successMessage: '',
    },
    created: function () {
        $.ajax({
            url: '/comment',
            context: this,
            dataType: 'json',
            success: function (data) {
                this.comments = data;
            },
            error: function () {
                this.showErrorMessage('Could not fetch messages.');
            },
        });
    },
    methods: {
        onSubmit(evt) {
            evt.preventDefault();

            $.ajax({
                url: '/comment/new',
                type: 'POST',
                context: this,
                dataType: 'json',
                data: this.form,
                success: function () {
                    this.comments.push(
                        {
                            email: this.form.email,
                            text: this.form.text,
                        },
                    );

                    this.form.text = '';
                    this.form.email = '';
                    document.getElementById("comment-form").reset();

                    this.showSuccessMessage('Comment has been added.');
                },
                error: function (data) {
                    let errorMessage = 'Invalid field values:';
                    for (const [field, errors] of Object.entries(data.responseJSON)) {
                        errors.forEach((error) => {
                            errorMessage = errorMessage.concat(`<br>${field}: ${error}.`);
                        });
                    }
                    this.showErrorMessage(errorMessage)
                },
            });
        },
        showSuccessMessage: function (message) {
            this.error = false;
            this.success = true;
            this.successMessage = message;
        },
        showErrorMessage: function (message) {
            this.error = true;
            this.success = false;
            this.errorMessage = message;
        }
    }
});
