{{-- Any other server error (502, 504…): the status code with a general explanation. --}}
@extends('errors.layout', ['code' => $exception->getStatusCode(), 'texts' => '5xx'])
