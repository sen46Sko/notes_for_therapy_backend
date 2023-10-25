@extends('admin.master')

@section('content')
    <section class="content-wrapper">
        <div class="container-fluid">
            <div class="row">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
    <form action="{{ route('coupons.store') }}" class="form-control" method="POST">
        @csrf

        <label for="code">Coupon Code:</label>
        <input type="text" id="code" class="form-control" name="code">

        <label for="discount">Days:</label>
        <input type="number" id="days" class="form-control" name="days">

        <label for="expiration">Expiration:</label>
        <input type="datetime-local" id="expiration" class="form-control" name="expiration">

        <button type="submit">Create Coupon</button>
    </form>
</div>
        </div>
    </section>
@endsection