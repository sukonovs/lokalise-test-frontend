describe('App test', function () {

    before(browser => browser.url('http://localhost:8000/'));

    test('Adds comment', function (browser) {
        browser
            .waitForElementVisible('.navbar-brand')
            .assert.titleContains('Guest Book')
            .assert.visible('input[type=email]')
            .setValue('input[type=email]', 'test@test.lv')
            .assert.visible('textarea#text')
            .setValue('textarea#text', 'Text from test')
            .assert.visible('button[type=submit]')
            .click('button[type=submit]')
            .assert.containsText('.alert', 'Comment has been added.')
            .assert.containsText('.card-link', 'test@test.lv')
            .assert.containsText('.card-text', 'Text from test')
    });

    after(browser => browser.end());
});
