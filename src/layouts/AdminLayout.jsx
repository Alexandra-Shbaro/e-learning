import React from "react";
import { Outlet } from "react-router-dom";

const AdminLayout = () => {
    return (
        <div className="admin-layout">
            <header>
                <h2>Admin Dashboard</h2>
            </header>
            <main>
                <Outlet /> 
            </main>
        </div>
    );
};

export default AdminLayout;
