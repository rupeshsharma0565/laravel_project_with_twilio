<div>

    @if(session('error'))
    <div style="color:green">
        {{ session('error')}}
    </div>
    @endif


    <h1>Login with Otp </h1>

    <form action="{{route('otp.generate')}}" method="POST">
        @csrf
        <label>Moblie Number</label>
        <input type="text" name="mobile_no" value="{{ old('mobile_no') }}">
        </br>
        <br>
        @error('mobile_no')
        <strong style="color:red;">
            {{ $message}}
        </strong>
        @enderror
        <button type="submit"> Genarate Otp </button>
    </form>

</div>