import React from "react";
import { Outlet } from "react-router-dom";

const Layout = ({ userType }) => {

    return (
        <div className="layout">
            <aside className="sidebar">
                <h2>{userType === "student" ? "Student Sidebar" : "Instructor Sidebar"}</h2>
                <ul>
                    <li>Dashboard</li>
                    <li>Profile</li>
                    <li>Settings</li>
                    {userType === "student" && <li>Student only option</li>}
                </ul>
            </aside>
            <main className="content">
                <Outlet />
            </main>
        </div>
    );
};

export default Layout;
