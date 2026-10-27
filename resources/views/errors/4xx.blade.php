{{-- Any other client error (400, 405, 414…): the status code with a general explanation. --}}
@extends('errors.layout', ['code' => $exception->getStatusCode(), 'texts' => '4xx'])
