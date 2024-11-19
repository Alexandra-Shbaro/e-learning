import React from "react";
import { BrowserRouter as Router, Routes, Route, Navigate } from "react-router-dom";
import useAuth from "../../utilities/hooks/useAuth";
import Login from "../../pages/Login";
import Signup from "../../pages/Signup";
import Layout from "../../layouts/Layout";
import AdminLayout from "../../layouts/AdminLayout";
import StudentDashboard from "../../dashboard-variants/StudentDashboard";
import AdminDashboard from "../../dashboard-variants/AdminDashboard";
import InstructorDashboard from "../../dashboard-variants/InstructorDashboard";

const AppRouter = () => {
    const { logged_in, user_type } = useAuth();

    return (
        <Router>
            <Routes>
                {/* Redirect logged-in users from login and signup */}
                <Route
                    path="/login"
                    element={logged_in ? <Navigate to="/dashboard" /> : <Login />}
                />
                <Route
                    path="/signup"
                    element={logged_in ? <Navigate to="/dashboard" /> : <Signup />}
                />

                {/* Role-based Layouts */}
                {logged_in && user_type === "student" && (
                    <Route path="/dashboard" element={<StudentDashboard />} />
                )}
                {logged_in && user_type === "instructor" && (
                    <Route path="/" element={<Layout userType="instructor" />}>
                        <Route path="dashboard" element={<InstructorDashboard />} />
                    </Route>
                )}
                {logged_in && user_type === "admin" && (
                    <Route path="/" element={<AdminLayout />}>
                        <Route path="dashboard" element={<AdminDashboard />} />
                    </Route>
                )}

                {/* Fallback */}
                <Route
                    path="*"
                    element={<Navigate to={logged_in ? "/dashboard" : "/login"} />}
                />
            </Routes>
        </Router>
    );
};

export default AppRouter;
