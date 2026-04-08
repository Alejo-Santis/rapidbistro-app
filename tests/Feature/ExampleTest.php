<?php

test('the application redirects unauthenticated users to login', function () {
    $this->get('/')->assertRedirect('/login');
});
