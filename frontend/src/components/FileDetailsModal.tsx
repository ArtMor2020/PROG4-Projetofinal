"use client";
import { useEffect } from "react";

interface FileDetailsModalProps {
  file: { id: number; name: string; type: string; preview?: string } | null;
  onClose: () => void;
}

export default function FileDetailsModal({ file, onClose }: FileDetailsModalProps) {
  // Close modal on "Escape" key press
  useEffect(() => {
    const handleKeyDown = (e: KeyboardEvent) => {
      if (e.key === "Escape") {
        onClose();
      }
    };

    if (file) {
      document.addEventListener("keydown", handleKeyDown);
    }

    return () => {
      document.removeEventListener("keydown", handleKeyDown);
    };
  }, [file, onClose]);

  if (!file) return null;

  return (
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
          {file.preview ? (
            <img
              src={file.preview}
              alt={file.name}
              className="max-h-full max-w-full rounded"
            />
          ) : (
            <div className="text-gray-400">No preview available</div>
          )}
        </div>

        {/* Right side - File Info */}
        <div className="w-1/2 p-6 flex flex-col gap-4 text-white">
          <h2 className="text-xl font-semibold mb-2">File Details</h2>

          {/* Name field */}
          <input
            type="text"
            defaultValue={file.name}
            className="border px-3 py-2 rounded text-white"
            style={{ backgroundColor: "#25282A", borderColor: "#3A3C3D" }}
          />

          {/* Type dropdown */}
          <select
            defaultValue={file.type}
            className="border px-3 py-2 rounded text-white"
            style={{ backgroundColor: "#25282A", borderColor: "#3A3C3D" }}
          >
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
              onClick={onClose}
            >
              Close
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
  );
}
