"use client";
import { useState } from "react";

interface AddFileModalProps {
  isOpen: boolean;
  onClose: () => void;
  onSave: (file: File | null, name: string, type: string, tags: string[]) => void;
}

export default function AddFileModal({ isOpen, onClose, onSave }: AddFileModalProps) {
  const [file, setFile] = useState<File | null>(null);
  const [name, setName] = useState("");
  const [type, setType] = useState("image");
  const [tags, setTags] = useState<string[]>([]);

  if (!isOpen) return null;

  const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    if (e.target.files && e.target.files[0]) {
      setFile(e.target.files[0]);
    }
  };

  const handleSave = () => {
    onSave(file, name, type, tags);
    onClose();
  };

  return (
    <div className="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50">
      <div
        className="rounded-lg shadow-lg w-[500px] max-w-full p-6 flex flex-col gap-4"
        style={{ backgroundColor: "#1E1F20", color: "white" }}
      >
        {/* Header */}
        <h2 className="text-xl font-semibold border-b border-gray-700 pb-2">
          Add File
        </h2>

        {/* File input + preview */}
        <div className="flex gap-4">
          <div className="flex-1 flex flex-col gap-2">
            <input type="file" onChange={handleFileChange} className="mb-2" />
            <input
              type="text"
              placeholder="File name..."
              value={name}
              onChange={(e) => setName(e.target.value)}
              className="border border-gray-600 bg-[#25282A] text-white px-2 py-1 rounded"
            />
            <select
              value={type}
              onChange={(e) => setType(e.target.value)}
              className="border border-gray-600 bg-[#25282A] text-white px-2 py-1 rounded"
            >
              <option value="image">Image</option>
              <option value="video">Video</option>
              <option value="doc">Document</option>
              <option value="audio">Audio</option>
              <option value="other">Other</option>
            </select>

            <button
              type="button"
              className="px-3 py-1 bg-green-600 text-white rounded"
              onClick={() => setTags([...tags, "Example Tag"])}
            >
              Select Tags
            </button>
          </div>

          {/* Preview */}
          <div className="w-32 h-32 border border-gray-600 flex items-center justify-center text-gray-400">
            {file ? (
              <img
                src={URL.createObjectURL(file)}
                alt="preview"
                className="object-cover w-full h-full rounded"
              />
            ) : (
              "Preview"
            )}
          </div>
        </div>

        {/* Actions */}
        <div className="flex justify-end gap-2 border-t border-gray-700 pt-2">
          <button
            onClick={onClose}
            className="px-4 py-1 bg-gray-600 rounded hover:bg-gray-500"
          >
            Cancel
          </button>
          <button
            onClick={handleSave}
            className="px-4 py-1 bg-blue-600 text-white rounded hover:bg-blue-500"
          >
            Save
          </button>
        </div>
      </div>
    </div>
  );
}
