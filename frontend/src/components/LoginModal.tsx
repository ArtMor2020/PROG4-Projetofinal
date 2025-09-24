"use client";

import React from "react";

interface LoginModalProps {
  isOpen: boolean;
  onClose: () => void;
}

export default function LoginModal({ isOpen, onClose }: LoginModalProps) {
  if (!isOpen) return null;

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

        <h2 className="text-2xl font-semibold mb-4 text-center">Login</h2>

        {/* Email Field */}
        <input
          type="email"
          placeholder="Email"
          className="w-full px-3 py-2 mb-3 border rounded-lg focus:outline-none focus:ring focus:ring-blue-300"
        />

        {/* Password Field */}
        <input
          type="password"
          placeholder="Password"
          className="w-full px-3 py-2 mb-4 border rounded-lg focus:outline-none focus:ring focus:ring-blue-300"
        />

        {/* Login Button */}
        <button className="w-full text-white py-2 rounded-lg hover:bg-blue-700 transition" style={{ backgroundColor: "#023DBF" }}>
          Login
        </button>
      </div>
    </div>
  );
}
