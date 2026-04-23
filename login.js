
// slide animation
document.getElementById("startBtn").onclick = () => {
  document.getElementById("bluePanel").classList.add("hide");
  setTimeout(()=> document.getElementById("formsArea").classList.add("show"), 400);
};

// LOGIN ACTION
document.getElementById("loginBtn").onclick = function(e){
    e.preventDefault();

    let u = document.getElementById("username").value.trim();
    let p = document.getElementById("password").value.trim();

    if(!u || !p){
        alert("Please enter username & password");
        return;
    }

    fetch("backend/login.php", {
        method: "POST",
        headers: {"Content-Type": "application/x-www-form-urlencoded"},
        body: "username=" + encodeURIComponent(u) + "&password=" + encodeURIComponent(p)

    })
    .then(r => r.text())
    .then(res => {

        if(res === "doctor"){
            window.location.href = "backend/doctor_dashboard.php";
        }
       else if(res === "patient"){
    window.location.href = "/hospital/index.php";
}

        else{
            alert("Invalid credentials");
        }
    });
};
