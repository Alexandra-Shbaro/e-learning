export async function getStudentCourses(student_id, token) {
    // Log student_id and token to check their values
    console.log("Student ID:", student_id);
    console.log("Token:", token);

    // Check if either student_id or token is missing
    if (!student_id || !token) {
        console.error("Missing student_id or token");
        return;
    }

    try {
        console.log("Fetching courses for student_id:", student_id);
        const res = await fetch(`http://localhost/e-learning-backend/getStudentCourses.php?student_id=${student_id}`, {
            method: 'GET',
            headers: {
                Authorization: `Bearer ${token}`, // Attach the token
                'Content-Type': 'application/json',
            },
        });

        if (!res.ok) {
            const errorMessage = await res.text();
            console.error(`Request failed with status: ${res.status} - ${errorMessage}`);
            return null;
        }

        const responseData = await res.json();
        console.log('Response Data:', responseData);
        return responseData;
    } catch (error) {
        console.error("Error fetching data:", error);
        return null;
    }
}
