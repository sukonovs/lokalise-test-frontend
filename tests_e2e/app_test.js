describe('App test', function () {

    before(browser => browser.url('http://localhost:8000/'));

    test('Adds comment', function (browser) {
        browser
            .waitForElementVisible('.navbar-brand')
            .assert.titleContains('Guest Book')
            .assert.visible('input#email')
            .setValue('input#email', 'test@test.lv')
            .assert.visible('textarea#text')
            .setValue('textarea#text', 'Text from test')
            .assert.visible('button#submit')
            .click('button#submit')
            .assert.containsText('.alert', 'Comment has been added.')
            .assert.containsText('div.comment-container > .comment:last-child .card-link', 'test@test.lv')
            .assert.containsText('div.comment-container > .comment:last-child .card-text', 'Text from test')
    });

    after(browser => browser.end());
});
