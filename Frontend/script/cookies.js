// === cookie.js ===
// Function to create a cookie
function createCookie(cookieName, cookieValue, daysToExpire) { 
    var date = new Date(); 
    date.setTime(date.getTime() + (daysToExpire * 24 * 60 * 60 * 1000)); 
    document.cookie = cookieName + "=" + cookieValue + "; expires=" + date.toGMTString() + "; path=/"; 
} 

// Function to read a cookie
function accessCookie(cookieName) { 
    var name = cookieName + "="; 
    var allCookieArray = document.cookie.split(';'); 
    for (var i = 0; i < allCookieArray.length; i++) { 
        var temp = allCookieArray[i].trim(); 
        if (temp.indexOf(name) == 0) 
            return temp.substring(name.length, temp.length); 
    } 
    return ""; 
}

// Function to delete a cookie
function deleteCookie(cookieName) {
    document.cookie = cookieName + "=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
}

function saveUser(event) {
    event.preventDefault(); // prevent form refresh
    var user = document.getElementById("email").value;
    createCookie("testCookie", user, 7); // store for 7 days
    alert("Welcome, " + user + "! Your email is saved in a cookie.");
}