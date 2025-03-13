@extends('layouts.app')

@section('title', 'Daftar Produk')

@section('content')
    <h1 class="text-xl font-bold mb-4">Daftar Produk</h1>

    <div x-data="{ search: '' }">
        <input type="text" x-model="search" placeholder="Cari produk..." 
               class="border p-2 w-full mb-4">

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($produks as $produk)
                <div x-show="{{ json_encode($produk->namaProduk) }}.toLowerCase().includes(search.toLowerCase())"
                     class="bg-white p-4 rounded shadow">
                    <h2 class="text-lg font-bold">{{ $produk->namaProduk }}</h2>
                    <p class="text-gray-600">Rp{{ number_format($produk->harga, 0, ',', '.') }}</p>
                    <p class="text-sm text-gray-500">{{ $produk->deskripsiProduk }}</p>
                </div>
            @endforeach
        </div>
    </div>
@endsection
