<div class="col-md-7">
    <div class="col-md-12">
        <div class="form-group margin-b-5 margin-t-5{{ $errors->has('first_name') ? ' has-error' : '' }}">
            <label for="name">First Name *</label>
            <input type="text" class="form-control" name="first_name" placeholder="First Name" value="{{ old('first_name', $record->first_name) }}" required>

            @if ($errors->has('first_name'))
                <span class="help-block">
                    <strong>{{ $errors->first('fist_name') }}</strong>
                </span>
            @endif
        </div>
        <!-- /.form-group -->
    </div>
    <!-- /.col-md-12 -->

    <div class="col-md-12">
        <div class="form-group margin-b-5 margin-t-5{{ $errors->has('last_name') ? ' has-error' : '' }}">
            <label for="name">Last Name *</label>
            <input type="text" class="form-control" name="last_name" placeholder="Last Name" value="{{ old('last_name', $record->last_name) }}" required>

            @if ($errors->has('last_name'))
                <span class="help-block">
                    <strong>{{ $errors->first('last_name') }}</strong>
                </span>
            @endif
        </div>
        <!-- /.form-group -->
    </div>
    <!-- /.col-md-12 -->

    <div class="col-md-12">
        <div class="form-group margin-b-5 margin-t-5{{ $errors->has('email') ? ' has-error' : '' }}">
            <label for="email">Email *</label>
            <input type="email" class="form-control" name="email" placeholder="Email" value="{{ old('email', $record->email) }}" required>

            @if ($errors->has('email'))
                <span class="help-block">
                    <strong>{{ $errors->first('email') }}</strong>
                </span>
            @endif
        </div>
        <!-- /.form-group -->
    </div>
    <!-- /.col-md-12 -->

</div>
<!-- /.col-md-7 -->

<div class="col-md-5">
    <div class="col-xs-12">
        <div class="form-group margin-b-5 margin-t-5">
            <label for="logo_number">Logo</label><br/>
            <div class="box box-info">
                <div class="box-body no-padding">
                    <ul class="logo-number users-list clearfix">
                    @foreach (\App\Attendize\Utils::getLogosNumber() as $logoNumber)
                        <li>
                            <img class="profile-user-img img-responsive img-circle" src="{{ \App\Attendize\Utils::logoPath($logoNumber) }}" alt="Profile picture {{ $logoNumber }}">
                            <span class="users-list-date">
                                <input type="radio" name="logo_number" value="{{ $logoNumber }}" {{ old('logo_number', $record->logo_number) == $logoNumber ? 'checked' : '' }}>
                            </span>
                        </li>
                    @endforeach
                    </ul>
                </div>
            </div>
        </div>
        <!-- /.form-group -->
    </div>
    <!-- /.col-xs-12 -->

</div>
<!-- /.col-md-5 -->
