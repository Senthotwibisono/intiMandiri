@extends('partial.main')
@section('custom_styles')

@endsection

@section('content')

<section>
    <div class="card">
        <div class="card-body">
            <table class="tabelCustom">
                <thead>
                    <tr>
                        <th class="text-center">Order No</th>
                        <th class="text-center">No HBL</th>
                        <th class="text-center">Tgl. HBL</th>
                        <th class="text-center">Quantity</th>
                        <th class="text-center">Customer</th>
                        <th class="text-center">Kasir</th>
                        <th class="text-center">Order At</th>
                        <th class="text-center">Pranota</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($headers as $form)
                        <tr>
                            <td class="text-center">{{$form->order_no}}</td>
                            <td class="text-center">{{$form->manifest->nohbl ?? ''}}</td>
                            <td class="text-center">{{$form->manifest->tgl_hbl ?? ''}}</td>
                            <td class="text-center">{{$form->manifest->quantity ?? ''}}</td>
                            <td class="text-center">{{$form->customer->name ?? ''}}</td>
                            <td class="text-center">{{$form->kasir->name ?? ''}}</td>
                            <td class="text-center">{{$form->order_at}}</td>
                            <td class="text-center">
                                <a type="button" href="/invoice/pranota-{{$form->id}}" target="_blank" class="btn btn-sm btn-warning text-white"><i class="fa fa-file"></i></a>
                            </td>
                            <td class="text-center">
                                <div class="button-container">
                                    <button class="btn btn-danger" data-id="{{ $form->id }}" id="deleteUser-{{ $form->id }}"><i class="fa fa-trash"></i></button>
                                    <button type="button" id="pay" data-id="{{$form->id}}" class="btn btn-sm btn-success pay"><i class="fa fa-cogs"></i></button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</section>


<div class="modal fade" id="editCust" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-centered modal-dialog-scrollable modal-sm"role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Payment Form</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"> <i data-feather="x"></i></button>
            </div>
            <form action="/invoice/paid" method="POST" id="updateForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="col-12">
                        <div class="form-group">
                            <label for="">Order No</label>
                            <input type="text" name="order_no" id="order_no_edit" class="form-control" readonly>
                            <input type="hidden" name="id" id="id_edit" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="">Grand Total</label>
                            <input type="text" name="grand_total" id="grand_total_edit" class="form-control" readonly>
                        </div>
                        <div class="form-group">
                            <label for="">Full/Piutang</label>
                            <select name="status" id="status_edit" class="form-select">
                                <option value="Y">Lunas</option>
                                <option value="P">Piutang</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal"> <i class="bx bx-x d-block d-sm-none"></i> <span class="d-none d-sm-block">Close</span> </button>
                    <button type="button" id="updateButton" class="btn btn-primary ml-1" data-bs-dismiss="modal"> <i class="bx bx-check d-block d-sm-none"></i> <span class="d-none d-sm-block">Submit</span> </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('custom_js')

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Attach event listener to the update button
        document.getElementById('updateButton').addEventListener('click', function (e) {
            e.preventDefault(); // Prevent the default form submission

            // Show SweetAlert confirmation dialog
            Swal.fire({
                title: 'Are you sure?',
                text: "Do you want to update this record?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, update it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Submit the form programmatically if confirmed
                    document.getElementById('updateForm').submit();
                }
            });
        });
    });
</script>

<script>
    document.getElementById('createForm').addEventListener('click', function() {
        fetch('/invoice/form/create', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({})
        }).then(response => response.json())
        .then(data => {
            if (data.id) {
                // Redirect to invoice step1 with the form ID
                window.location.href = `/invoice/form/formStep1/${data.id}`;
            }
        });
    });
</script>

<script>
    document.querySelectorAll('[id^="deleteUser-"]').forEach(button => {
    button.addEventListener('click', function() {
        var userId = this.getAttribute('data-id');

        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Anda tidak akan dapat mengembalikan ini!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`/invoice/deleteHeader-${userId}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                }).then(response => {
                    if (response.ok) {
                        Swal.fire(
                            'Dihapus!',
                            'Data invoice telah dihapus.',
                            'success'
                        ).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire(
                            'Gagal!',
                            'Data pengguna tidak dapat dihapus.',
                            'error'
                        );
                    }
                }).catch(error => {
                    Swal.fire(
                        'Gagal!',
                        'Terjadi kesalahan saat menghapus data pengguna.',
                        'error'
                    );
                });
            }
        });
    });
});
</script>

<script>
   $(document).on('click', '.pay', function() {
    let id = $(this).data('id');
    $.ajax({
      type: 'GET',
      url: '/invoice/actionButton-' + id,
      cache: false,
      data: {
        id: id
      },
      dataType: 'json',

      success: function(response) {

        console.log(response);
        $('#editCust').modal('show');
        $("#editCust #id_edit").val(response.data.id);
        $("#editCust #order_no_edit").val(response.data.order_no);
        $("#editCust #grand_total_edit").val(response.data.grand_total);
        
      },
      error: function(data) {
        console.log('error:', data)
      }
    });
  });
</script>
@endsection