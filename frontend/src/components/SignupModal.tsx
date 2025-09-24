"use client";

import React, { useState } from "react";

interface SignupModalProps {
  isOpen: boolean;
  onClose: () => void;
}

export default function SignupModal({ isOpen, onClose }: SignupModalProps) {
  const [password, setPassword] = useState("");
  const [confirmPassword, setConfirmPassword] = useState("");
  const [passwordError, setPasswordError] = useState("");

  if (!isOpen) return null;

  const handleSignup = () => {
    if (password !== confirmPassword) {
      setPasswordError("Passwords do not match");
      return;
    }
    setPasswordError("");
    alert("Signup successful (mock)");
    onClose();
  };

  return (
    <div className="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
      <div className="rounded-xl shadow-lg p-6 w-96 relative" style={{ backgroundColor: "#1E2022" }}>
        {/* Close Button */}
        <button
          onClick={onClose}
          className="absolute top-2 right-2 text-gray-500 hover:text-gray-800"
        >
          ✕
        </button>

        <h2 className="text-2xl font-semibold mb-4 text-center">Sign Up</h2>

        {/* Email Field */}
        <input
          type="email"
          placeholder="Email"
          className="w-full px-3 py-2 mb-3 border rounded-lg focus:outline-none focus:ring focus:ring-green-300"
        />

        {/* Password Field */}
        <input
          type="password"
          placeholder="Password"
          value={password}
          onChange={(e) => setPassword(e.target.value)}
          className="w-full px-3 py-2 mb-3 border rounded-lg focus:outline-none focus:ring focus:ring-green-300"
        />

        {/* Confirm Password Field */}
        <input
          type="password"
          placeholder="Confirm Password"
          value={confirmPassword}
          onChange={(e) => setConfirmPassword(e.target.value)}
          className="w-full px-3 py-2 mb-2 border rounded-lg focus:outline-none focus:ring focus:ring-green-300"
        />

        {/* Error Message */}
        {passwordError && (
          <p className="text-red-600 text-sm mb-2">{passwordError}</p>
        )}

        {/* Sign Up Button */}
        <button
          onClick={handleSignup}
          className="w-full text-white py-2 rounded-lg hover:bg-green-700 transition"
          style={{ backgroundColor: "#008532" }}
        >
          Sign Up
        </button>
      </div>
    </div>
  );
}
