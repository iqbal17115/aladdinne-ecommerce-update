<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Become A Seller - {{ config('app.name', 'ReadyEcommerce') }}</title>

    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/leaflet.css') }}">
    <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
</head>
<style>
    body {
        background-color: #FFFFFF !important;
    }

    .wrapper {
        min-height: 100svh;
        display: flex;
    }

    .promotionSection {
        width: 35%;
        background-image: url("{{ asset('assets/images/shop-register.png') }}");
        background-position: center;
        background-repeat: no-repeat;
        background-size: cover;
    }

    .registerFormSection {
        width: 65%;
        display: flex;
        flex-direction: column;
        row-gap: 24px;
    }

    @media (max-width: 767px) {
        .wrapper {
            flex-direction: column;
        }

        .promotionSection {
            display: none;
        }

        .registerFormSection {
            width: 100%;
        }
    }

    .step-indicators {
        display: flex;
        column-gap: 32px;
    }

    .indicator {
        width: 32px;
        height: 32px;
        padding: 4px;
        border-width: 1px;
        border-style: solid;
        border-color: var(--bs-border-color);
        border-radius: 50%;
    }

    .indicator.active {
        border-color: var(--theme-color);
    }

    .indicator-devider {
        width: 100%;
        top: 16px;
        left: 32px;
        border-bottom-width: 2px;
        border-bottom-style: dashed;
        border-color: var(--bs-border-color);
    }

    .step {
        display: flex;
        flex-direction: column;
        row-gap: 32px;
    }

    .information {
        position: relative;
        display: flex;
        flex-direction: column;
        padding: 32px 16px 16px;
        gap: 20px;
        isolation: isolate;
        border: 1px solid #D7DAE0;
        border-radius: 16px;
    }

    .title {
        font-weight: 500;
        font-size: 24px;
        line-height: 32px;
        padding: 0px 2px;
        position: absolute;
        left: 64px;
        top: -16px;
        background: #FFFFFF;
    }
</style>

<body>

    <div class="wrapper">
        <div class="promotionSection">
        </div>

        <div class="registerFormSection ps-3 pe-3 pe-md-0 py-4">
            <div class="d-flex column-gap-2 align-items-center d-none" id="backBtn" style="cursor: pointer">
                <i class="fa fa-arrow-left"></i>Back
            </div>
            <div class="d-flex flex-column" style="row-gap: 40px">
                <div class="d-flex justify-content-between align-items-center pe-md-4 pb-2 border-bottom">
                    <h3 class="mb-0" style="font-weight: 600">
                        </i> {{ __('Register as a seller') }}
                    </h3>
                    <div class="step-indicators">
                        <div class="position-relative">
                            <div class="indicator active d-flex justify-content-center align-items-center"
                                id="indicator1">
                                1
                            </div>
                            User information
                            <div class="indicator-devider position-absolute">
                            </div>
                        </div>
                        <div>
                            <div class="indicator d-flex justify-content-center align-items-center" id="indicator2">
                                2
                            </div>
                            Shop information
                        </div>
                    </div>
                </div>
                <div class="pe-md-4">
                    <form action="{{ route('shop.register.submit') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="step" id="step1">
                            <div style="display: flex; flex-direction: column; row-gap: 24px">
                                <div class="information">
                                    <div class="title">
                                        {{ __('User Information') }}
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="row">
                                                <div class="col-md-7">
                                                    <x-file name="profile_photo" label="User profile (Ratio 1:1)"
                                                        preview="previewProfile" required="true" />
                                                    <p class="text text-danger m-0" id="profile_photo_error"></p>
                                                </div>
                                                <div class="col-md-5 mt-4 mt-md-0">
                                                    <div class="d-flex align-items-center justify-content-center">
                                                        <div class="ratio1x1">
                                                            <img id="previewProfile"
                                                                src="https://placehold.co/500x500/png" alt=""
                                                                width="100%">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mt-3">
                                                        <x-input label="First Name" name="first_name" type="text"
                                                            placeholder="Enter Name" required="true" />
                                                        <p class="text text-danger m-0" id="first_name_error"></p>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mt-3">
                                                        <x-input label="Last Name" name="last_name" type="text"
                                                            placeholder="Enter Name" />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-me-12">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mt-3">
                                                        <x-input label="Phone Number" name="phone" type="number"
                                                            placeholder="Enter phone number" required="true" />
                                                        <p class="text text-danger m-0" id="phone_error"></p>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mt-3">
                                                        <x-select label="Gender" name="gender">
                                                            <option value="male">{{ __('Male') }}</option>
                                                            <option value="female">{{ __('Female') }}</option>
                                                        </x-select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="information">
                                    <div class="title">
                                        {{ __('Account Information') }}
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <x-input type="email" name="email" label="Email Address"
                                                placeholder="Enter Email Address" required="true" />
                                            <p class="text text-danger m-0" id="email_error"></p>
                                        </div>
                                        <div class="col-md-12">
                                            {{-- <div class="row">
                                                <div class="col-md-6 mt-3">
                                                    <x-input type="password" name="password" label="Password"
                                                        placeholder="Enter Password" required="true" />
                                                    <p class="text text-danger m-0" id="password_error"></p>
                                                </div>
                                                <div class="col-md-6 mt-3">
                                                    <x-input type="password" name="password_confirmation"
                                                        label="Confirm Password" placeholder="Enter Confirm Password"
                                                        required="true" />
                                                    <p class="text text-danger m-0" id="password_confirmation_error">
                                                    </p>
                                                </div>
                                            </div> --}}
                                            <div class="row">
                                                <div class="col-md-6 mt-3">
                                                    <label class="form-label">Password</label>
                                                    <div class="input-group">
                                                        <input type="password" name="password" id="password"
                                                            class="form-control" placeholder="Enter Password"
                                                            required>
                                                        <span class="input-group-text" style="cursor:pointer"
                                                            onclick="togglePassword('password','toggleIcon1')">
                                                            <i class="fa fa-eye" id="toggleIcon1"></i>
                                                        </span>
                                                    </div>
                                                    <p class="text text-danger m-0" id="password_error"></p>
                                                </div>
                                                <div class="col-md-6 mt-3">
                                                    <label class="form-label">Confirm Password</label>
                                                    <div class="input-group">
                                                        <input type="password" name="password_confirmation"
                                                            id="password_confirmation" class="form-control"
                                                            placeholder="Enter Confirm Password" required>
                                                        <span class="input-group-text" style="cursor:pointer"
                                                            onclick="togglePassword('password_confirmation','toggleIcon2')">
                                                            <i class="fa fa-eye" id="toggleIcon2"></i>
                                                        </span>
                                                    </div>
                                                    <p class="text text-danger m-0" id="password_confirmation_error">
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-primary py-2.5" id="nextBtn">Next</button>
                        </div>
                        <div class="step" id="step2" style="display: none">
                            <div class="information">
                                <div class="title">
                                    {{ __('Shop Information') }}
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <x-input type="text" name="shop_name" label="Shop Name"
                                            placeholder="Enter Shop Name" required="true" />
                                    </div>

                                    <div class="col-md-12 mt-4">
                                        <x-input type="text" name="address" label="Address"
                                            placeholder="Enter Address" />
                                    </div>

                                    <div class="col-md-12">
                                        <div class="row">
                                            <div class="col-md-7 mt-4">
                                                <x-file name="shop_logo" label="Shop Profile Image ( Ratio 1:1 )"
                                                    preview="previewShopLogo" required="true" />
                                            </div>
                                            <div class="col-md-5 mt-4">
                                                <div class="d-flex align-items-center justify-content-center">
                                                    <div class="ratio1x1">
                                                        <img src="https://placehold.co/500x500/png"
                                                            id="previewShopLogo" alt="" width="100%">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12 mt-4">
                                        <x-file name="shop_banner" label="Shop banner Ratio 4:1 (2000 x 500 px)"
                                            preview="shopBanner" required="true" />
                                        <div class="d-flex align-items-center justify-content-center mt-2">
                                            <div class="ratio4x1">
                                                <img src="https://placehold.co/2000x500/png" id="shopBanner"
                                                    alt="" width="100%">
                                            </div>
                                        </div>
                                    </div>
                                    <input type="hidden" id="latitude" name="latitude"
                                        value="{{ old('latitude') }}">
                                    <input type="hidden" id="longitude" name="longitude"
                                        value="{{ old('longitude') }}">
                                    <div id="map" style="height:400px; border-radius: 10px;margin-top: 20px">
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary py-2.5">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/scripts/jquery-3.6.3.min.js') }}"></script>
    <script src="{{ asset('assets/scripts/leaflet.js') }}"></script>
    <script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
    <script>
        $(function() {
            $('#nextBtn').on('click', function() {
                if (!validateStep()) {
                    return;
                }

                $('#step1').hide();
                $('#step2').show();
                $('#backBtn').removeClass('d-none');
                $('#indicator1').removeClass('active');
                $('#indicator2').addClass('active');
                setTimeout(function() {
                    map.invalidateSize();
                }, 200);
            });

            $('#backBtn').on('click', function() {
                $('#step2').hide();
                $(this).addClass('d-none');;
                $('#step1').show();
                $('#indicator2').removeClass('active');
                $('#indicator1').addClass('active');
            });

            $('#step1 input[required]').on('input', function() {
                $(this).removeClass('is-invalid');
                $('#' + $(this).attr('name') + '_error').text('')
            });
        });

        function validateStep() {
            let valid = true;

            const profilePhoto = $('input[name=profile_photo]');
            const firstName = $('input[name=first_name]');
            const phone = $('input[name=phone]');
            const email = $('input[name=email]');
            const password = $('input[name=password]');
            const passwordConfirmation = $('input[name=password_confirmation]');

            function setError(input, errorId, message) {
                $(errorId).text(message);
                input.addClass('is-invalid');
                valid = false;
            }

            function clearError(input, errorId) {
                $(errorId).text('');
                input.removeClass('is-invalid');
            }

            if (!profilePhoto.val()) {
                setError(profilePhoto, '#profile_photo_error', 'Profile photo is required.');
            } else {
                clearError(profilePhoto, '#profile_photo_error');
            }

            if (!firstName.val()) {
                setError(firstName, '#first_name_error', 'First name is required.');
            } else {
                clearError(firstName, '#first_name_error');
            }

            if (!phone.val()) {
                setError(phone, '#phone_error', 'Phone number is required.');
            } else {
                clearError(phone, '#phone_error');
            }

            if (!email.val()) {
                setError(email, '#email_error', 'Email is required.');
            } else {
                clearError(email, '#email_error');
            }

            if (!password.val()) {
                setError(password, '#password_error', 'Password is required.');
            } else if (password.val().length < 6) {
                setError(password, '#password_error', 'Password must be at least 6 characters long.');
            } else {
                clearError(password, '#password_error');
            }

            if (!passwordConfirmation.val()) {
                setError(passwordConfirmation, '#password_confirmation_error', 'Password confirmation is required.');
            } else if (password.val() !== passwordConfirmation.val()) {
                setError(passwordConfirmation, '#password_confirmation_error', 'Passwords do not match.');
            } else {
                clearError(passwordConfirmation, '#password_confirmation_error');
            }

            return valid;
        }

        var previewFile = (event, previewID) => {
            var reader = new FileReader();
            reader.onload = function() {
                var output = document.getElementById(previewID);
                output.src = reader.result;
            };
            reader.readAsDataURL(event.target.files[0]);
        };

        function checkDescription() {
            if (document.getElementById('description').value.length > 200) {
                document.getElementById('descriptionError').innerHTML =
                    'Description must be less than or equal to 220 characters';
            } else {
                document.getElementById('descriptionError').innerHTML = '';
            }
        }
    </script>
    <script>
        let lat = 24.1307138;
        let lng = 39.7824587;

        const map = L.map('map').setView([lat, lng], 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);

        const shopIcon = L.icon({
            iconUrl: '{{ asset('assets/icons/home.png') }}',
            iconSize: [35, 35],
            iconAnchor: [17, 35],
            popupAnchor: [0, -30],
            shadowUrl: null
        });

        let marker = L.marker([lat, lng], {
            draggable: true,
            icon: shopIcon
        }).addTo(map);

        function updateLatLng(lat, lng) {
            document.getElementById('latitude').value = lat;
            document.getElementById('longitude').value = lng;
        }

        updateLatLng(lat, lng);

        map.on('click', function(e) {
            marker.setLatLng(e.latlng);
            updateLatLng(e.latlng.lat, e.latlng.lng);
        });

        marker.on('dragend', function(e) {
            const pos = marker.getLatLng();
            updateLatLng(pos.lat, pos.lng);
        });

        L.Control.geocoder({
                defaultMarkGeocode: false
            })
            .on('markgeocode', function(e) {
                var latlng = e.geocode.center;
                marker.setLatLng(latlng);
                map.setView(latlng, 15);
                updateLatLng(latlng.lat, latlng.lng);
            })
            .addTo(map);
    </script>
    <script>
        function togglePassword(fieldId, iconId) {
            let field = document.getElementById(fieldId);
            let icon = document.getElementById(iconId);

            if (field.type === "password") {
                field.type = "text";
                icon.classList.remove("fa-eye");
                icon.classList.add("fa-eye-slash");
            } else {
                field.type = "password";
                icon.classList.remove("fa-eye-slash");
                icon.classList.add("fa-eye");
            }
        }
    </script>
</body>

</html>
