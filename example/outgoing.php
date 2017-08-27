#!/usr/bin/env php
<?php

require __DIR__.'/../vendor/autoload.php';

use ElfSundae\BearyChat\Message;

$message = (new Message)
    ->text('Response Text')
    ->add('Response Attachment');

echo json_encode($message);
