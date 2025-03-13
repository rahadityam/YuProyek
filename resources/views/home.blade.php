@extends('layouts.app')

@section('title', 'Home')

@section('content')
    <h1 class="text-xl font-bold">Selamat Datang di Aplikasi</h1>

    <div x-data="{ show: false }">
        <button @click="show = !show" class="bg-blue-500 text-white px-4 py-2 mt-4">Tampilkan Pesan</button>
        <p x-show="show" class="mt-2 text-gray-700">Halo! Ini pesan dari Alpine.js.</p>
    </div>
@endsection
