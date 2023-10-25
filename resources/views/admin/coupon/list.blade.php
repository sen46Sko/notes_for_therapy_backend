@extends('admin.master')

@section('content')
    <style type="text/css">
        .navigation-nre{
            margin-bottom: 35px;
            text-align: center;
        }

        .navigation-nre span svg{
            width: 2%
        }
        .navigation-nre nav div:first-child{
            display: none;
        }

        .block-and-rd{
            /*    display: flex;
                gap:20px;*/
        }

        .switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            -webkit-transition: .4s;
            transition: .4s;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            -webkit-transition: .4s;
            transition: .4s;
        }

        input:checked + .slider {
            background-color: #2196F3;
        }

        input:focus + .slider {
            box-shadow: 0 0 1px #2196F3;
        }

        input:checked + .slider:before {
            -webkit-transform: translateX(26px);
            -ms-transform: translateX(26px);
            transform: translateX(26px);
        }

        /* Rounded sliders */
        .slider.round {
            border-radius: 34px;
        }

        .slider.round:before {
            border-radius: 50%;
        }

    </style>
    <!-- Styles -->


    <section class="content-wrapper">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h2 class="m-0">Coupon</h2>

                    @if (Session::has('success'))
                        <div class="alert alert-success">
                            {{ Session::get('success') }}
                        </div>
                    @endif
                </div><!-- /.col -->
                <div class="col-12">
                    <div class="card">
                        <!-- /.card-header -->

                        <a href="{{route('coupons.create')}}"> Add Coupon</a>
                        <div class="card-body">
                            <table class="table table-bordered table-hover">
                                <thead>
                                <tr>
                                    <th>S.No</th>
                                    <th>code</th>
                                    <th>Days</th>
                                    <th>Status</th>
                                    <th>Expiration</th>
                                </tr>
                                </thead>
                                <tbody>

                                @foreach($coupons as $key => $coupon)

                                    <tr>
                                        <td>{{ ($coupons->currentpage()-1) * $coupons->perpage() + $key + 1 }}</td>
                                        <td>{{$coupon->code}}</td>
                                        <td>{{$coupon->days}}</td>
                                        <td>{{$coupon->active}}</td>
                                        <td>{{$coupon->expiration}}</td>
                                        <td><button type="button" onclick="getCoupon({{$coupon->id}},'{{$coupon->code}}')" d class="btn btn-primary" data-toggle="modal" data-target="#exampleModal">
                                               Assign Promocode
                                            </button></td>
                                        <td><a href="{{route('coupon.delete',$coupon->id)}}" class="btn btn-primary"><i class="fa fa-trash"></i></a> </td>
                                        <!-- Button trigger modal -->


                                        <!-- Modal -->


                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="col-md-12">
                            <div class="navigation-nre">
                                {{$coupons->links()}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <form action="{{route('coupon.assign')}}" method="POST">
                                @csrf
                                <input type="text" class="form-control" id="couponIdInput" name="couponId" readonly>
                                <input type="hidden" class="form-control" id="id" name="id">

                                <table id="userstable"  class="table table-bordered table-hover">
                                    <thead>
                                    <tr>

                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Assign Coupon</th>
                                    </tr>
                                    </thead>
                                    <tbody>

                                    </tbody>
                                </table>
                                <div class="col-md-12">
                                    <div class="navigation-nre">

                                    </div>
                                </div>
                                <button class="btn btn-primary" type="submit">Assign Coupon</button>
                            </form>

                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
    </section>
    <!-- CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.3/css/dataTables.bootstrap4.min.css">

    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.3/js/dataTables.bootstrap4.min.js"></script>


    <script>

        jQuery(document).ready(function($) {

        });
      function getCoupon(id,code){
          jQuery.noConflict();
          $('#userstable').DataTable({
              processing: true,
              serverSide: true,
              ajax: {
                  url: '{{ route('coupon.user') }}',
                  data: function (d) {
                      d.coupon_id = id; // Replace `yourCouponId` with the actual coupon ID value
                  }
              },
              columns: [
                  { data: 'name', name: 'name' },
                  { data: 'email', name: 'email' },
                  { data: 'action', name: 'action', orderable: false, searchable: false }
              ]
          });
   document.getElementById('couponIdInput').value =code;
   document.getElementById('id').value =id;
      }
    </script>
@endsection