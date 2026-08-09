<?php extract((new \Illuminate\Support\Collection($attributes->getAttributes()))->mapWithKeys(function ($value, $key) { return [Illuminate\Support\Str::camel(str_replace([':', '.'], ' ', $key)) => $value]; })->all(), EXTR_SKIP); ?>
@props(['title','user','notificationsCount'])
<x-faculty-portal :title="$title" :user="$user" :notifications-count="$notificationsCount" >

{{ $slot ?? "" }}
</x-faculty-portal>