<?php

test('the landing page is public and does not redirect to login', function () {
    $this->get('/')->assertStatus(200);
});
