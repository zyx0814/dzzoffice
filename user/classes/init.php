<?php

namespace user\classes;

use \C;

class Init {
    public function dzzInitbefore() {

        $cachelist = ['usergroups', 'fields_register'];

        $dzz = C::app();

        $dzz->cachelist = $cachelist;

    }
}