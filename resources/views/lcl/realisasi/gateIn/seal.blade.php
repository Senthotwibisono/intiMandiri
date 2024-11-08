@extends('partial.main')

@section('content')
<section>
    <div class="card">
        <div class="card-body fixed-height-cardBody">
            <br>
            <div class="table-responsive">
                <table class="tabelCustom table table-bordered table-striped" style="overflow-x:auto;">
                    <thead>
                        <tr>
                            <th>Action</th>
                            <th>No Job Order</th>
                            <th>No SPK</th>
                            <th>No Container</th>
                            <th>No MBL</th>
                            <th>DO</th>
                            <th>Tgl Disapatche</th>
                            <th>Jam Disapatche</th>
                            <th>ETA</th>
                            <th>Vessel</th>
                            <th>Seal</th>
                            <th>UID</th>
                        </tr>
                        <tbody>
                            @foreach($conts as $cont)
                                <tr>
                                    <td>
                                        <div class="button-container">
                                            <buttpn class="btn btn-outline-warning editButton" data-id="{{$cont->id}}"><i class="fa fa-pen"></i></buttpn>
                                            <a href="javascript:void(0)" onclick="openWindow('/lcl/realisasi/gateIn-detail{{$cont->id}}')" class="btn btn-sm btn-info"><i class="fa fa-eye"></i></a>
                                            @if($cont->no_seal != null)
                                                @if($cont->status_dispatche == 'Y')
                                                    <button class="btn btn-danger closeDO" data-id="{{$cont->id}}">Close DO</button>
                                                @else
                                                    <button class="btn btn-primary sendEasyGo" data-id="{{$cont->id}}">Dispatche E-Seal</button>
                                                @endif
                                            @endif
                                        </div>
                                    </td>
                                    <td>{{$cont->job->nojoborder}}</td>
                                    <td>{{$cont->job->nospk}}</td>
                                    <td>{{$cont->nocontainer}}</td>
                                    <td>{{$cont->job->nombl}}</td>
                                    <td>{{$cont->do_id ?? ''}}</td>
                                    <td>{{$cont->tgl_dispatche ?? ''}}</td>
                                    <td>{{$cont->jam_dispatche ?? ''}}</td>
                                    <td>{{$cont->job->eta}}</td>
                                    <td>{{$cont->job->Kapal->name ?? ''}}</td>
                                    <td>{{$cont->seal->code ?? ''}}</td>
                                    <td>{{$cont->user->name}}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</section>
<section>
    <div class="card">
        <div class="card-header">
            <strong>Form Input Gate In Data</strong>
        </div>
        <form action="{{ route('lcl.seal.update')}}" id="updateForm" method="post" enctype="multipart/form-data">
            <div class="card-body">
                @csrf
                <div class="row mt-2">
                    <div class="col-6">
                        <div class="form-group">
                            <label for="">No SPK</label>
                            <input type="text" name="nospk" id="nospk" class="form-control" readonly>
                        </div>
                        <div class="form-group">
                            <label for="">Container</label>
                            <input type="text" name="nocontainer" id="nocontainer" class="form-control" readonly>
                            <input type="hidden" name="id" id="id" class="form-control" readonly>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="">Size</label>
                                    <input type="text" name="size" id="size" class="form-control" readonly>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="">Weight</label>
                                    <input type="text" name="weight" id="weight" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label for="">Nomor Polisi</label>
                            <input type="text" name="nopol" id="nopol" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="">Seal</label>
                            <select name="no_seal" id="no_seal"  class="js-example-basic-single form-select select2" style="width: 100%;">
                                <option value disabled selected>Pilih Satu</option>
                                @foreach($seals as $seal)
                                    <option value="{{$seal->id}}">{{$seal->code}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="">Petugas Lapangan</label>
                            <input type="text" id="nameUid"value="{{$user}}" class="form-control" readonly>
                            <input type="hidden" name="uidmasuk" id="uidmasuk" class="form-control">
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="button" class="btn btn-outline-danger" id="cancelButton">Cancel</button>
                <button type="button" class="btn btn-outline-success updateButton" id="updateButton">Submit</button>        
            </div>
        </form>
    </div>
</section>

<div class="modal fade" id="editCust" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-centered modal-dialog-scrollable"role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Close DO</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"> <i data-feather="x"></i></button>
            </div>
            <form action="/lcl/realisasi/easyGo-closeDO" method="POST" id="updateForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="col-12">
                        <div class="form-group">
                            <label for="">Tgl POD</label>
                            <input type="datetime-local" name="tgl_pod" value="{{$now}}" class="form-control">
                            <input type="hidden" name="id" id="id" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="">Tgl Close</label>
                            <input type="datetime-local" name="tgl_closed" value="{{$now}}" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="">Keterangan Close</label>
                            <textarea class="form-control"  name="ket_close" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal"> <i class="bx bx-x d-block d-sm-none"></i> <span class="d-none d-sm-block">Close</span> </button>
                    <button type="submit" class="btn btn-primary ml-1" data-bs-dismiss="modal"> <i class="bx bx-check d-block d-sm-none"></i> <span class="d-none d-sm-block">Submit</span> </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('custom_js')
<script>
$(document).ready(function() {
    // When Cancel button is clicked
    $('#cancelButton').click(function() {
        // Reload the current page
        location.reload();
    });
});
</script>
<script>
    $(document).ready(function () {
        $('.sendEasyGo').on('click', function () {
            var contId = $(this).data('id');

            // SweetAlert confirmation dialog
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Anda akan mengirim E-Seal untuk container ini!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, kirim sekarang!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Prepare the data
                    var data = {
                        id: contId,
                        _token: '{{ csrf_token() }}' // Include CSRF token for security
                    };

                    // Send POST request to the controller
                    $.ajax({
                        url: '/lcl/realisasi/easyGo-send', // Replace with your actual route
                        type: 'POST',
                        data: data,
                        success: function (response) {
                            if (response.success) {
                                // Show success alert
                                Swal.fire(
                                    'Berhasil!',
                                    'E-Seal berhasil dikirim!',
                                    'success'
                                    ).then(() => {
                                    location.reload(); // Reload the page after success
                                });
                            } else {
                                // Show error alert
                                Swal.fire(
                                    'Gagal!',
                                    response.message,
                                    'error'
                                    ).then(() => {
                                    location.reload(); // Reload the page after success
                                });
                            }
                        },
                        error: function (xhr, status, error) {
                            // Show error alert
                            Swal.fire(
                                'Terjadi Kesalahan!',
                                'Gagal mengirim E-Seal. Silakan coba lagi.',
                                'error'
                                ).then(() => {
                                    location.reload(); // Reload the page after success
                                });
                        }
                    });
                }
            });
        });
    });
</script>
<script>
   $(document).on('click', '.editButton', function() {
    let id = $(this).data('id');
    $.ajax({
      type: 'GET',
      url: '/lcl/realisasi/gateIn-edt' + id,
      cache: false,
      data: {
        id: id
      },
      dataType: 'json',

      success: function(response) {

        console.log(response);
        $("#nospk").val(response.job.nospk);
        $("#nocontainer").val(response.data.nocontainer);
        $("#id").val(response.data.id);
        $("#size").val(response.data.size);
        $("#weight").val(response.data.weight);
        $("#tglmasuk").val(response.data.tglmasuk);
        $("#jammasuk").val(response.data.jammasuk);
        $("#nopol").val(response.data.nopol);
        $("#no_seal").val(response.data.no_seal).trigger('change');
        $("#uidmasuk").val(response.data.uid.id ?? response.userId);
        $("#nameUid").val(response.uid.name ?? response.user);
      },
      error: function(data) {
        console.log('error:', data)
      }
    });
  });
</script>


<script>
   $(document).on('click', '.closeDO', function() {
    let id = $(this).data('id');
    $.ajax({
      type: 'GET',
      url: '/lcl/realisasi/gateIn-edt' + id,
      cache: false,
      data: {
        id: id
      },
      dataType: 'json',

      success: function(response) {

        console.log(response);
        $('#editCust').modal('show');
        $("#editCust #id").val(response.data.id);
        

        
      },
      error: function(data) {
        console.log('error:', data)
      }
    });
  });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Attach event listener to the update button
        document.getElementById('updateButton').addEventListener('click', function (e) {
            e.preventDefault(); // Prevent the default form submission

            // Show SweetAlert confirmation dialog
            Swal.fire({
                title: 'Are you sure?',
                text: "",
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
    function openWindow(url) {
        window.open(url, '_blank', 'width=600,height=800');
    }
</script>
@endsection