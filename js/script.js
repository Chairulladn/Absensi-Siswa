document.addEventListener("DOMContentLoaded", function () {

    const rfidInput = document.getElementById("rfid_uid");

    if (!rfidInput) {
        return;
    }

    // Fokus awal
    rfidInput.focus();


    // RFID reader biasanya mengirim Enter
    rfidInput.addEventListener("keydown", function (event) {

        if (event.key === "Enter") {

            event.preventDefault();

            if (rfidInput.value.trim() !== "") {

                rfidInput.form.submit();

            }

        }

    });


    // Setelah halaman selesai diproses PHP,
    // siapkan kembali input untuk tap berikutnya
    window.addEventListener("load", function () {

        rfidInput.value = "";

        rfidInput.focus();

    });


    // Kalau operator klik area halaman,
    // fokus dikembalikan ke input RFID
    document.addEventListener("click", function (event) {

        // Jangan ganggu link/sidebar
        if (
            event.target.tagName !== "A" &&
            event.target.tagName !== "BUTTON"
        ) {

            rfidInput.focus();

        }

    });

});