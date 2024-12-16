document.getElementById("loginForm").addEventListener("submit", function (e) {
    e.preventDefault(); // Prevent form from submitting normally

    const username = document.getElementById("username").value;
    const password = document.getElementById("password").value;

    fetch("login.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded",
        },
        body: `username=${encodeURIComponent(username)}&password=${encodeURIComponent(password)}`,
    })
        .then((response) => response.json())
        .then((data) => {
            if (data.success) {
                alert(data.message); // Show success message
                location.reload(); // Reload the page or redirect
            } else {
                const loginError = document.getElementById("loginError");
                loginError.textContent = data.message;
                loginError.classList.remove("d-none");
            }
        })
        .catch((error) => console.error("Error:", error));
});