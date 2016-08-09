<?php

class TestFailedException extends Exception { 
    public function __toString() {
        return "exception 'Test failed' with message\n$this->message";
    }
}
