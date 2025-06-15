function fetchLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function (position) {
                document.getElementById("latitude").value = position.coords.latitude;
                document.getElementById("longitude").value = position.coords.longitude;
            },
            function () {
                alert("Location access denied. Attendance can't be marked.");
            }
        );
        return true;
    } else {
        alert("Geolocation not supported.");
        return false;
    }
}
