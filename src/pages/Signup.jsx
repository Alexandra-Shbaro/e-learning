import React, { useState } from "react";
import useSignup from "../utilities/hooks/useSignup"; // Import the custom hook
import { useNavigate } from "react-router-dom";

const Signup = () => {
  const [username, setUsername] = useState(""); // Add username field
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const { signup, loading, error, success } = useSignup(); // Use the custom hook
  const navigate = useNavigate()

  const handleSignup = async (e) => {
    e.preventDefault();

    const result = await signup(username, email, password);

    if (result.success) {
      console.log("User created successfully with ID:", result.user_id);
      window.location.reload();
    } else {
      console.error("Signup failed:", result.message);
    }
  };

  return (
    <div className="signup-container">
      <h2>Sign Up</h2>
      <form onSubmit={handleSignup}>
        <div>
          <label>Username:</label>
          <input
            type="text"
            value={username}
            onChange={(e) => setUsername(e.target.value)}
            required
          />
        </div>
        <div>
          <label>Email:</label>
          <input
            type="email"
            value={email}
            onChange={(e) => setEmail(e.target.value)}
            required
          />
        </div>
        <div>
          <label>Password:</label>
          <input
            type="password"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            required
          />
        </div>
        {error && <p className="error">{error}</p>} {/* Show error */}
        {success && <p className="success">Signup successful!</p>} {/* Show success message */}
        <button type="submit" disabled={loading}>
          {loading ? "Signing up..." : "Sign Up"}
        </button>
      </form>
    </div>
  );
};

export default Signup;
