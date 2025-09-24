"use client";

import { useState } from "react";
import LoginModal from "@/components/LoginModal";
import SignupModal from "@/components/SignupModal";

export default function Home() {
  const [showLogin, setShowLogin] = useState(false);
  const [showSignup, setShowSignup] = useState(false);

  return (
    <div className="flex flex-col items-center justify-center min-h-screen relative" style={{ backgroundColor: "#1E2022" }}>
      {/* App Title */}
      <h1 className="text-4xl font-bold mb-6">TagScribe!</h1>

      {/* Buttons */}
      <div className="flex space-x-4">
        <button
          onClick={() => setShowLogin(true)}
          className="px-6 py-2 text-white rounded-lg shadow hover:bg-blue-700 transition"
          style={{ backgroundColor: "#023DBF" }}
        >
          LOGIN
        </button>
        <button
          onClick={() => setShowSignup(true)}
          className="px-6 py-2 text-white rounded-lg shadow hover:bg-green-700 transition"
          style={{ backgroundColor: "#008532" }}
        >
          SIGN UP
        </button>
      </div>

      {/* Modals */}
      <LoginModal isOpen={showLogin} onClose={() => setShowLogin(false)} />
      <SignupModal isOpen={showSignup} onClose={() => setShowSignup(false)} />
    </div>
  );
}
