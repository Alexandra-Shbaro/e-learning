import React from 'react'
import { useEffect } from 'react';
import { useState } from 'react';
import { getStudentCourses } from '../utilities/api/student-utils';
import useAuth from '../utilities/hooks/useAuth';


function StudentDashboard() {
    const { token, user_id } = useAuth(); // Assuming user_id and token are available from useAuth
    const [courses, setCourses] = useState([]);

    useEffect(() => {
        if (user_id && token) {
            getStudentCourses(user_id, token)
                .then(responseData => {
                    if (responseData) {
                        setCourses(responseData);
                    }
                })
                .catch(error => {
                    console.error("Error fetching courses:", error);
                });
        }
    }, [user_id, token]);

    return (
        <>

            <div className="dashboard-container">
                <div className="courses-container">
                    <h1>My Courses</h1>
                    {Array.isArray(courses) && courses.length > 0 ? (
                        courses.map((dataObj, index) => (
                            <div
                                key={dataObj.course_id}
                                style={{
                                    width: "15em",
                                    backgroundColor: "#35D841",
                                    padding: 2,
                                    borderRadius: 10,
                                    marginBlock: 10,
                                }}
                            >
                                <p style={{ fontSize: 20, color: 'white' }}>{dataObj.course_name}</p>
                                <p>{dataObj.course_description}</p>
                            </div>
                        ))
                    ) : (
                        <p>No courses available</p>
                    )}
                </div>

                <div className="streams-container">
                    <h1>streams</h1>

                    <p>this is where the streams go</p>
                </div>
                <div className="comments">
                    <div className="public-comments">
                        <h1>here are public comments</h1>
                        <p>anotha one</p>
                    </div>
                    <div className="private-comments">
                        <h1>here are private comments</h1>
                        <p>anotha one</p>
                    </div>
                </div>
            </div>

        </>

    )
}

export default StudentDashboard