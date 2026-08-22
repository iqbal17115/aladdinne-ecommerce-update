@extends('layouts.app')
@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <h4>{{ __('Update Area') }}</h4>
                <form action="{{ route('admin.area.update', $area->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <x-input type="text" name="name" label="Name" placeholder="Name" required="true"
                                value="{{ $area->name }}" />
                        </div>

                        <div class="col-md-3 mb-3">
                            <x-input type="number" name="delivery_amount" label="Delivery Amount"
                                placeholder="Delivery Amount" required="true" onlyNumber="true"
                                value="{{ $area->delivery_amount }}" />
                        </div>

                        {{-- <div class="col-md-3 mb-3">
                            <x-input type="number" name="distance" label="Coverage Radius (in km)"
                                placeholder="Coverage Radius" value="{{ $area->distance }}" />
                        </div> --}}

                        <div class="col-md-3 mb-3">
                            <x-input type="text" id="latitude" name="latitude" label="Latitude" placeholder="Latitude"
                                value="{{ $area->latitude }}" />
                        </div>

                        <div class="col-md-3 mb-3">
                            <x-input type="text" id="longitude" name="longitude" label="Longitude"
                                placeholder="Longitude" value="{{ $area->longitude }}" />
                        </div>

                        <div class="col-md-3 mb-3">
                            <x-input type="number" step="0.01" name="price_per_km" label="Price per km"
                                placeholder="Price per km" value="{{ $area->price_per_km }}" />
                        </div>

                        <div class="col-md-3 mb-3">
                            <x-input type="number" step="0.01" name="price_per_min" label="Price per min"
                                placeholder="Price per min" value="{{ $area->price_per_min }}" />
                        </div>

                        <div class="col-md-3 mt-4 form-check">
                            <input type="checkbox" class="form-check-input" name="is_active" id="is_active"
                                {{ $area->is_active ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">{{ __('Active') }}</label>
                        </div>
                    </div>

                    <input type="hidden" id="polygon_coordinates" name="polygon_coordinates" value="{{ $area->polygon_coordinates }}">

                    <div class="mb-3">
                        <label>{{ __('Search / Draw Coverage Area on Map') }}</label>
                        <div id="map" style="height: 450px; margin-top:10px"></div>
                        <div class="mt-2">
                            <button type="button" id="clearPolygon" class="btn btn-sm btn-danger" style="display:none">
                                <i class="fa fa-trash"></i> {{ __('Clear Polygon') }}
                            </button>
                            <small class="text-muted ms-2">{{ __('Use the polygon tool in the map toolbar to draw the coverage area.') }}</small>
                        </div>
                    </div>

                    <button class="btn btn-primary" type="submit">{{ __('Update') }}</button>

                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var lat = parseFloat('{{ $area->latitude ?? 23.8103 }}');
            var lng = parseFloat('{{ $area->longitude ?? 90.4125 }}');
            if (isNaN(lat)) lat = 23.8103;
            if (isNaN(lng)) lng = 90.4125;

            var map = L.map('map').setView([lat, lng], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19
            }).addTo(map);

            const areaIcon = L.icon({
                iconUrl: '{{ asset('assets/icons/map-pin.png') }}',
                iconSize: [35, 35],
                iconAnchor: [17, 35],
                popupAnchor: [0, -30],
                shadowUrl: null
            });

            var marker = L.marker([lat, lng], {
                draggable: true,
                icon: areaIcon
            }).addTo(map);

            var drawnItems = new L.FeatureGroup();
            map.addLayer(drawnItems);

            var drawControl = new L.Control.Draw({
                position: 'topleft',
                draw: {
                    polygon: {
                        allowIntersection: false,
                        showArea: true,
                        shapeOptions: {
                            color: '#0d6efd',
                            fillColor: '#0d6efd',
                            fillOpacity: 0.15,
                            weight: 2
                        }
                    },
                    polyline: false,
                    rectangle: false,
                    circle: false,
                    circlemarker: false,
                    marker: false
                },
                edit: {
                    featureGroup: drawnItems,
                    edit: true,
                    remove: true
                }
            });
            map.addControl(drawControl);

            var savedCoords = '{{ $area->polygon_coordinates }}';
            if (savedCoords && savedCoords !== '') {
                try {
                    var coords = JSON.parse(savedCoords);
                    if (coords.length > 0) {
                        var polygon = L.polygon(coords, {
                            color: '#0d6efd',
                            fillColor: '#0d6efd',
                            fillOpacity: 0.15,
                            weight: 2
                        }).addTo(drawnItems);
                        map.fitBounds(polygon.getBounds().pad(0.1));
                        document.getElementById('clearPolygon').style.display = 'inline-block';
                    }
                } catch (e) {
                    console.log('Invalid polygon coordinates');
                }
            }

            function syncPolygonData() {
                var coords = [];
                drawnItems.eachLayer(function(layer) {
                    if (layer instanceof L.Polygon) {
                        var latlngs = layer.getLatLngs()[0];
                        latlngs.forEach(function(ll) {
                            coords.push([ll.lat, ll.lng]);
                        });
                    }
                });
                document.getElementById('polygon_coordinates').value = coords.length ? JSON.stringify(coords) : '';
                document.getElementById('clearPolygon').style.display = coords.length ? 'inline-block' : 'none';
            }

            map.on('draw:created', function(e) {
                drawnItems.eachLayer(function(layer) {
                    drawnItems.removeLayer(layer);
                });
                drawnItems.addLayer(e.layer);
                syncPolygonData();
            });

            map.on('draw:edited', syncPolygonData);
            map.on('draw:deleted', syncPolygonData);

            document.getElementById('clearPolygon').addEventListener('click', function() {
                drawnItems.eachLayer(function(layer) {
                    drawnItems.removeLayer(layer);
                });
                syncPolygonData();
            });

            var circle = null;

            function updateCircle() {
                if (circle) map.removeLayer(circle);
                var latlng = marker.getLatLng();
                var distance = parseFloat(document.getElementById('distance').value);
                if (distance && distance > 0) {
                    circle = L.circle([latlng.lat, latlng.lng], {
                        radius: distance * 1000,
                        color: '#0d6efd',
                        fillColor: '#0d6efd',
                        fillOpacity: 0.1,
                        weight: 2
                    }).addTo(map);
                    map.fitBounds(circle.getBounds().pad(0.1));
                }
            }

            var initialDistance = parseFloat('{{ $area->distance ?? 0 }}');
            if (initialDistance > 0) {
                setTimeout(updateCircle, 300);
            }

            document.getElementById('distance').addEventListener('input', updateCircle);

            marker.on('dragend', function(e) {
                var latlng = marker.getLatLng();
                document.getElementById('latitude').value = latlng.lat;
                document.getElementById('longitude').value = latlng.lng;
                updateCircle();
            });

            var geocoder = L.Control.geocoder({
                    defaultMarkGeocode: false
                })
                .on('markgeocode', function(e) {
                    var latlng = e.geocode.center;
                    marker.setLatLng(latlng);
                    map.setView(latlng, 15);
                    document.getElementById('latitude').value = latlng.lat;
                    document.getElementById('longitude').value = latlng.lng;
                    updateCircle();
                })
                .addTo(map);
        });
    </script>
@endpush
