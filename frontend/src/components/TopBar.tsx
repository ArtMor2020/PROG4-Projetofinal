"use client";
import { useState } from "react";
import { useRouter } from "next/navigation";

interface TopBarProps {
  columns: number;
  setColumns: (cols: number) => void;
}

export default function TopBar({ columns, setColumns }: TopBarProps) {
  const router = useRouter();
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [file, setFile] = useState<File | null>(null);
  const [previewUrl, setPreviewUrl] = useState<string | null>(null);

  const handleLogout = () => {
    const confirmed = window.confirm("Are you sure you want to logout?");
    if (confirmed) {
      router.push("/");
    }
  };

  const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const selectedFile = e.target.files?.[0] || null;
    setFile(selectedFile);
    if (selectedFile) {
      setPreviewUrl(URL.createObjectURL(selectedFile));
    } else {
      setPreviewUrl(null);
    }
  };

  return (
    <>
      {/* Top bar */}
      <div
        className="flex items-center justify-between p-2 shadow-sm"
        style={{ backgroundColor: "#181A1B" }}
      >
        {/* Left side */}
        <div className="flex items-center gap-2">
          <button
            className="px-3 py-1 text-white rounded"
            style={{ backgroundColor: "#0047B2" }}
            onClick={() => setIsModalOpen(true)}
          >
            Add File
          </button>
          <input
            type="text"
            placeholder="Search files..."
            className="border px-2 py-1 rounded text-white"
            style={{ backgroundColor: "#25282A", borderColor: "#3A3C3D" }}
          />
        </div>

        {/* Filters */}
        <div className="flex items-center gap-2">
          {["Images", "Videos", "Docs", "Audios", "Others"].map((label) => (
            <button
              key={label}
              className="px-3 py-1 rounded text-white"
              style={{ backgroundColor: "#25282A" }}
            >
              {label}
            </button>
          ))}

          {/* Dropdown for columns */}
          <select
            value={columns}
            onChange={(e) => setColumns(Number(e.target.value))}
            className="border px-2 py-1 rounded text-white"
            style={{ backgroundColor: "#25282A", borderColor: "#3A3C3D" }}
          >
            <option value={2}>2 columns</option>
            <option value={3}>3 columns</option>
            <option value={4}>4 columns</option>
            <option value={5}>5 columns</option>
            <option value={6}>6 columns</option>
          </select>
        </div>

        {/* Logout Button */}
        <button
          onClick={handleLogout}
          className="px-3 py-1 text-white rounded"
          style={{ backgroundColor: "#B1030C" }}
        >
          Logout
        </button>
      </div>

      {/* Add File Modal */}
      {isModalOpen && (
        <div className="fixed inset-0 flex items-center justify-center bg-black bg-opacity-70 z-50">
          <div
            className="rounded-lg shadow-lg flex w-3/4 max-w-5xl h-[70vh] overflow-hidden"
            style={{ backgroundColor: "#181A1B" }}
          >
            {/* Left side - Preview */}
            <div
              className="w-1/2 flex items-center justify-center p-6"
              style={{ backgroundColor: "#202324" }}
            >
              {previewUrl ? (
                <img
                  src={previewUrl}
                  alt="Preview"
                  className="max-h-full max-w-full rounded"
                />
              ) : (
                <div className="text-gray-400">File preview will appear here</div>
              )}
            </div>

            {/* Right side - Form */}
            <div className="w-1/2 p-6 flex flex-col gap-4 text-white">
              <h2 className="text-xl font-semibold mb-2">Add New File</h2>

              {/* File input */}
              <input
                type="file"
                onChange={handleFileChange}
                className="border px-3 py-2 rounded text-white"
                style={{ backgroundColor: "#25282A", borderColor: "#3A3C3D" }}
              />

              {/* Name field */}
              <input
                type="text"
                placeholder="Enter file name"
                className="border px-3 py-2 rounded text-white"
                style={{ backgroundColor: "#25282A", borderColor: "#3A3C3D" }}
              />

              {/* Type dropdown */}
              <select
                className="border px-3 py-2 rounded text-white"
                style={{ backgroundColor: "#25282A", borderColor: "#3A3C3D" }}
              >
                <option value="">Select type</option>
                <option value="image">Image</option>
                <option value="video">Video</option>
                <option value="doc">Document</option>
                <option value="audio">Audio</option>
                <option value="other">Other</option>
              </select>

              {/* Select tags button */}
              <button
                className="px-3 py-2 rounded text-white"
                style={{ backgroundColor: "#0047B2" }}
              >
                Select Tags
              </button>

              {/* Actions */}
              <div className="flex justify-end gap-3 mt-auto">
                <button
                  className="px-4 py-2 rounded text-white"
                  style={{ backgroundColor: "#B1030C" }}
                  onClick={() => setIsModalOpen(false)}
                >
                  Cancel
                </button>
                <button
                  className="px-4 py-2 rounded text-white"
                  style={{ backgroundColor: "#0047B2" }}
                >
                  Save
                </button>
              </div>
            </div>
          </div>
        </div>
      )}
    </>
  );
}
