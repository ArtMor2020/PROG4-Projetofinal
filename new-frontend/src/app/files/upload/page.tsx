"use client";

import { useState, useEffect } from "react";
import { useRouter } from "next/navigation";

interface Tag {
  name: string;
  description: string;
  color: string;
}

export default function UploadFilePage() {
  const router = useRouter();
  const [file, setFile] = useState<File | null>(null);
  const [preview, setPreview] = useState<string | null>(null);
  const [tags, setTags] = useState<Tag[]>([{ name: "", description: "", color: "#ffffff" }]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const token = typeof window !== "undefined" ? localStorage.getItem("token") : null;

  useEffect(() => {
    if (!token) router.push("/");
  }, [token, router]);

  // Handle file selection & preview
  const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    if (!e.target.files) return;
    const selectedFile = e.target.files[0];
    setFile(selectedFile);

    const reader = new FileReader();
    reader.onload = () => setPreview(reader.result as string);
    reader.readAsDataURL(selectedFile);
  };

  // Handle tag updates
  const handleTagChange = (index: number, field: keyof Tag, value: string) => {
    const newTags = [...tags];
    newTags[index][field] = value;
    setTags(newTags);
  };

  const addTag = () => setTags([...tags, { name: "", description: "", color: "#ffffff" }]);
  const removeTag = (index: number) => setTags(tags.filter((_, i) => i !== index));

  // Submit file + tags
  const handleSubmit = async () => {
    if (!file) {
      setError("Please select a file.");
      return;
    }

    setLoading(true);
    setError(null);

    const formData = new FormData();
    formData.append("file", file);
    formData.append("tags", JSON.stringify(tags));

    try {
      const res = await fetch("/api/file/upload", {
        method: "POST",
        headers: {
          Authorization: `Bearer ${token}`,
        },
        body: formData,
      });

      if (!res.ok) {
        const data = await res.json();
        throw new Error(data.message || "Upload failed");
      }

      router.push("/files"); // go back to files page
    } catch (err: any) {
      setError(err.message);
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div
      className="flex flex-col items-center min-h-screen p-6"
      style={{ backgroundColor: "#1E2022", color: "white" }}
    >
      <h1 className="text-3xl font-bold mb-6">Upload File</h1>

      {error && <p className="text-red-500 mb-4">{error}</p>}

      {/* File input & preview */}
      <div className="mb-6 w-full max-w-md">
        <input
          type="file"
          onChange={handleFileChange}
          className="w-full p-2 rounded bg-gray-700 text-white"
        />
        {preview && (
          <div className="mt-4 flex justify-center">
            {file?.type.startsWith("image") ? (
              <img src={preview} alt="preview" className="max-h-60 object-contain rounded" />
            ) : (
              <p className="text-center">{file?.name}</p>
            )}
          </div>
        )}
      </div>

      {/* Dynamic tags */}
      <div className="w-full max-w-2xl mb-6">
        <h2 className="text-xl font-semibold mb-2">Tags</h2>
        {tags.map((tag, index) => (
          <div
            key={index}
            className="flex gap-2 mb-2 p-2 rounded bg-gray-800 items-center"
          >
            <input
              type="text"
              placeholder="Name"
              value={tag.name}
              onChange={(e) => handleTagChange(index, "name", e.target.value)}
              className="flex-1 p-1 rounded bg-gray-700"
            />
            <input
              type="text"
              placeholder="Description"
              value={tag.description}
              onChange={(e) => handleTagChange(index, "description", e.target.value)}
              className="flex-1 p-1 rounded bg-gray-700"
            />
            <input
              type="color"
              value={tag.color}
              onChange={(e) => handleTagChange(index, "color", e.target.value)}
              className="w-12 h-8 p-0 rounded border border-gray-500"
            />
            {tags.length > 1 && (
              <button
                onClick={() => removeTag(index)}
                className="text-red-400 hover:text-red-600"
              >
                ✕
              </button>
            )}
          </div>
        ))}
        <button
          onClick={addTag}
          className="mt-2 px-4 py-1 bg-blue-600 rounded hover:bg-blue-700 transition"
        >
          + Add Tag
        </button>
      </div>

      {/* Actions */}
      <div className="flex gap-4">
        <button
          onClick={handleSubmit}
          disabled={loading}
          className="px-6 py-2 bg-green-600 rounded shadow hover:bg-green-700 transition"
        >
          {loading ? "Uploading..." : "Submit"}
        </button>
        <button
          onClick={() => router.push("/files")}
          className="px-6 py-2 bg-gray-600 rounded shadow hover:bg-gray-700 transition"
        >
          Cancel
        </button>
      </div>
    </div>
  );
}
