<?php

arch('it will not use debugging functions')
    ->expect(['dd', 'dump', 'ray'])
    ->each->not->toBeUsed();

arch('it will not use base carbon')
    ->expect('\\Carbon\\Carbon')
    ->not->toBeUsed();
