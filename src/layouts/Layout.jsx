import React from "react";
import { Outlet } from "react-router-dom";
import logo from '../images/logo11.png';
import { logOut } from "../utilities/logout/logout-utils";

const Layout = ({ userType }) => {

    return (
        <div className="layout">
            <nav className="navbar">
                <div className="navbar-left">
                    <img src={logo} alt="Logo" className="logo" />
                </div>
                <div className="navbar-center">
                    <a href="/dashboard" className="nav-link">Dashboard</a>
                    <a href="/courses" className="nav-link">Courses</a>
                    <a href="/assignments" className="nav-link">Assignments</a>
                    {userType === "student" && <a href="/assignments" className="nav-link">Studen tonlt</a>}

                </div>
                <div className="navbar-right">
                    <button className="logout-button" onClick={logOut}>Logout</button>
                </div>
            </nav>
            <main className="content">
                <Outlet />
            </main>
        </div>
    );
};

export default Layout;
