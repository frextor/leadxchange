@extends('emails.layout')
@php $emailTitle = $emailTitle ?? 'LeadXchange'; @endphp

@section('content')
{!! $content !!}
@endsection
