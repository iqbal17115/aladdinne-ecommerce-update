@extends('layouts.app')
@section('header-title', __('All Drivers'))

@section('content')
    <div class="d-flex align-items-center flex-wrap gap-3 justify-content-between px-3">

        <h4>
            {{ __('All Drivers') }}
        </h4>
    </div>

    <div class="container-fluid mt-3">

        <div class="mb-3 card">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2 py-3">
                <h5 class="card-title m-0"> {{ __('Drivers') }}</h5>

                <div class="d-flex gap-3 flex-wrap">
                    @hasPermission('admin.rider.create')
                        <a href="{{ route('admin.rider.create') }}" class="btn btn-primary py-2">
                            <i class="fa fa-plus-circle"></i> {{ __('Add Driver') }}
                        </a>
                    @endhasPermission
                    <div class="dropdown" >
                        <a class="btn border py-2 text-start dropdown-toggle w-100" href="#" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            {{ __(request()->status ? ucfirst(request()->status) : 'All') }}
                        </a>
                        <ul class="dropdown-menu w-100">
                            <li>
                                <a class="dropdown-item" href="{{ route('admin.rider.index') }}">
                                    {{ __('All') }}
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('admin.rider.index', 'status=pending') }}">
                                    {{ __('Pending') }}
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('admin.rider.index', 'status=approved') }}">
                                    {{ __('Approved') }}
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table border-left-right table-responsive-md">
                        <thead class="table-light">
                            <tr>
                                <th>SL.</th>
                                <th>{{ __('Profile') }}</th>
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('Phone') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th class="text-center">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        @forelse($riders as $key => $user)
                            @php
                                $serial = $riders->firstItem() + $key;
                            @endphp
                            <tr>
                                <td>{{ $serial }}</td>
                                <td>
                                    <img class="rounded-circle" src="{{ $user->thumbnail }}" width="40" height="40"
                                        loading="lazy" />
                                </td>
                                <td>{{ $user->fullName }}</td>

                                <td>
                                    {{ $user->phone }}
                                </td>

                                @hasPermission('admin.rider.index')
                                    <td>
                                        <label class="switch mb-0" data-bs-toggle="tooltip" data-bs-placement="left"
                                            data-bs-title="Change Active Status">
                                            <a href="{{ route('admin.rider.toggle', $user->id) }}" class="confirm">
                                                <input type="checkbox" {{ $user->is_active ? 'checked' : '' }}>
                                                <span class="slider round"></span>
                                            </a>
                                        </label>
                                    </td>
                                @endhasPermission

                                <td class="text-center">
                                    @hasPermission('admin.rider.show')
                                        <a href="{{ route('admin.rider.show', $user->id) }}" class="btn  svg-bg circleIcon">
                                            <img src="{{ asset('assets/icons-admin/eye.svg') }}" alt="view"
                                                loading="lazy" />
                                        </a>
                                    @endhasPermission
                                    @hasPermission('admin.rider.edit')
                                        <a href="{{ route('admin.rider.edit', $user->id) }}"
                                            class="btn  btn-outline-info circleIcon">
                                            <img src="{{ asset('assets/icons-admin/edit.svg') }}" alt="edit"
                                                loading="lazy" />
                                        </a>
                                    @endhasPermission
                                    @if ($user->driver->driverLocation()->exists())
                                        <button type="button" class="btn btn-outline-success circleIcon" id="riderLocation"
                                            data-id="{{ $user->driver->id }}" data-bs-toggle="modal"
                                            data-bs-target="#riderLocationModal">
                                            <i class="fa-solid fa-location-dot"></i>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="text-center" colspan="100%">{{ __('No Data Found') }}</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="my-3">
            {{ $riders->withQueryString()->links() }}
        </div>

    </div>

    <!--rider Modal -->
    <div class="modal fade" id="riderLocationModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">Rider Location</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="map" style="height: 70vh; width: 100%;"></div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        $(".confirm").on("click", function(e) {
            e.preventDefault();
            const url = $(this).attr("href");
            Swal.fire({
                title: "Are you sure?",
                text: "You want to change active status!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, Change it!",
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            });
        });
    </script>

    <script>
    function animateMarker(marker, from, to, duration = 1000) {
        const start = performance.now();

        function animate(time) {
            const progress = Math.min((time - start) / duration, 1);

            const lat = from.lat + (to.lat - from.lat) * progress;
            const lng = from.lng + (to.lng - from.lng) * progress;

            marker.setLatLng([lat, lng]);

            if (progress < 1) {
                requestAnimationFrame(animate);
            }
        }

        requestAnimationFrame(animate);
    }
</script>


<script>
    let map = null;
    let riderMarker = null;
    let riderId = null;

    function initMap(lat, lng) {
        map = L.map('map').setView([lat, lng], 15);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        riderMarker = L.marker([lat, lng], {
            icon: L.icon({
                iconUrl: '{{ asset('assets/icons/pin-map.png') }}',
                iconSize: [35, 35],
                iconAnchor: [17, 35]
            })
        }).addTo(map).bindPopup('Rider Live Location');
    }

    function subscribeToRiderLocation(riderId) {


        channel = pusher.subscribe('rider-location.' + riderId);

        channel.bind('rider.location.updated', function (data) {

            if (!riderMarker || data.location.driver_id !== riderId) {
                return;
            }

            const latitude = data.location.latitude;
            const longitude = data.location.longitude;

            moveMarkerSmooth(riderMarker, latitude, longitude, 2000);

            // riderMarker.setLatLng([latitude, longitude]);
            map.panTo([latitude, longitude], { animate: true });
        });
    }

    $(document).on('click', '#riderLocation', function () {
        riderId = $(this).data('id');

        $('#riderLocationModal').modal('show');

        $.get("{{ route('admin.rider.location', ':id') }}".replace(':id', riderId), function (res) {
            console.log(res.data.location,'res');

            const { latitude, longitude } = res.data.location;

            $('#riderLocationModal').on('shown.bs.modal', function () {

                if (map) {
                    map.remove();
                    map = null;
                }

                initMap(latitude, longitude);
                subscribeToRiderLocation(riderId);

                setTimeout(() => map.invalidateSize(), 300);
            });
        });
    });

    $('#riderLocationModal').on('hidden.bs.modal', function () {

        if (channel) {
            pusher.unsubscribe('rider-location.' + riderId);
            channel = null;
        }

        if (map) {
            map.remove();
            map = null;
        }
    });
</script>

@endpush
