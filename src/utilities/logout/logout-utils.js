export function logOut() {
    localStorage.removeItem("jwtToken")
    window.location.reload()
}