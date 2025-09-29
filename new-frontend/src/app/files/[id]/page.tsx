"use client";

import { useState, useEffect } from "react";
import { useParams, useRouter } from "next/navigation";

interface FileInfo {
  id: string;
  id_owner: string;
  name: string;
  type: string;
  path: string;
  is_deleted: string;
}

interface Tag {
  id: string;
  id_owner: string;
  name: string;
  description: string;
  color: string;
}

interface FileTag {
  id: string;
  id_file: string;
  id_tag: string;
}

export default function FileEditorPage() {
  const { id } = useParams();
  const router = useRouter();
  const token = typeof window !== "undefined" ? localStorage.getItem("token") : null;

  const [file, setFile] = useState<FileInfo | null>(null);
  const [fileContent, setFileContent] = useState<string | null>(null); // base64 content
  const [name, setName] = useState("");
  const [tags, setTags] = useState<Tag[]>([]);
  const [associations, setAssociations] = useState<FileTag[]>([]);
  const [newTag, setNewTag] = useState<{ name: string; description: string; color: string }>({
    name: "",
    description: "",
    color: "#ffffff",
  });
  const [fullscreen, setFullscreen] = useState(false);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  // helper: map file.type to mime
  const mimeForType = (f: FileInfo | null) => {
    if (!f) return "application/octet-stream";
    switch ((f.type || "").toUpperCase()) {
      case "IMAGE":
        return "image/jpeg";
      case "VIDEO":
        return "video/mp4";
      case "DOCUMENT":
        return "application/pdf";
      default:
        return "application/octet-stream";
    }
  };

  useEffect(() => {
    if (!token) {
      router.push("/");
      return;
    }
    if (!id) {
      router.push("/files");
      return;
    }

    const fetchData = async () => {
      try {
        // 1) fetch file metadata
        const resFile = await fetch(`/api/files/${id}`, {
          headers: { Authorization: `Bearer ${token}` },
        });
        if (!resFile.ok) throw new Error("Failed to fetch file info");
        const fileData: FileInfo = await resFile.json();
        setFile(fileData);
        setName(fileData.name);

        // 2) fetch file content (base64)
        const resContent = await fetch(`/api/file/${id}`, {
          headers: { Authorization: `Bearer ${token}` },
        });
        if (resContent.ok) {
          const contentJson = await resContent.json();
          setFileContent(contentJson?.content ?? null);
        } else {
          // not fatal — we still can show name etc.
          console.warn("No file content returned:", resContent.status);
          setFileContent(null);
        }

        // 3) associations between file and tags
        const resAssoc = await fetch(`/api/file-tags/file/${id}`, {
          headers: { Authorization: `Bearer ${token}` },
        });
        if (!resAssoc.ok) {
          setAssociations([]);
        } else {
          const assocData: FileTag[] = await resAssoc.json();
          setAssociations(assocData);

          // 4) fetch the tag objects for each association
          const tagPromises = assocData.map((a) =>
            fetch(`/api/tags/${a.id_tag}`, {
              headers: { Authorization: `Bearer ${token}` },
            }).then((r) => {
              if (!r.ok) throw new Error("Failed to load tag " + a.id_tag);
              return r.json();
            })
          );
          const tagsData = await Promise.all(tagPromises);
          setTags(tagsData);
        }
      } catch (err: any) {
        console.error(err);
        setError("Failed to load file info");
      }
    };

    fetchData();
  }, [id, token, router]);

  // Save file name
  const handleSave = async () => {
    if (!file) return;

    // check if name changed
    if (name.trim() === file.name) {
      // nothing to save, just go back
      router.push("/files");
      return;
    }

    setLoading(true);
    try {
      const res = await fetch(`/api/files/${file.id}`, {
        method: "PUT",
        headers: {
          "Content-Type": "application/json",
          Authorization: `Bearer ${token}`,
        },
        body: JSON.stringify({ name: name.trim() }),
      });

      if (!res.ok) throw new Error("Failed to save file");

      // success — navigate back
      router.push("/files");
    } catch (err) {
      console.error(err);
      setError("Failed to save changes");
    } finally {
      setLoading(false);
    }
  };


  // Delete file
  const handleDelete = async () => {
    if (!file) return;
    if (!confirm("Are you sure you want to delete this file?")) return;
    try {
      const res = await fetch(`/api/files/${file.id}`, {
        method: "DELETE",
        headers: { Authorization: `Bearer ${token}` },
      });
      if (!res.ok) throw new Error("Delete failed");
      router.push("/files");
    } catch (err) {
      console.error(err);
      setError("Failed to delete file");
    }
  };

  // Unlink tag association (assocId)
  const unlinkTag = async (assocId: string) => {
    try {
      const res = await fetch(`/api/file-tags/${assocId}`, {
        method: "DELETE",
        headers: { Authorization: `Bearer ${token}` },
      });
      if (!res.ok) throw new Error("Failed to unlink tag");

      // remove assoc
      const removedAssoc = associations.find((a) => a.id === assocId);
      setAssociations((prev) => prev.filter((a) => a.id !== assocId));
      if (removedAssoc) {
        setTags((prevTags) => prevTags.filter((t) => t.id !== removedAssoc.id_tag));
      }
    } catch (err) {
      console.error("Failed to unlink tag", err);
      setError("Failed to unlink tag");
    }
  };

  // Add tag and link it to the file
  const addTag = async () => {
    if (!file) return;
    if (!newTag.name.trim()) {
      setError("Tag name required");
      return;
    }
    setLoading(true);
    try {
      // create tag (backend handles duplicates)
      const resTag = await fetch(`/api/tags`, {
        method: "POST",
        headers: {
          Authorization: `Bearer ${token}`,
        },
        body: JSON.stringify({
          name: newTag.name,
          description: newTag.description,
          color: newTag.color,
        }),
      });

      if (!resTag.ok) {
        const txt = await resTag.text();
        console.error("Tag create failed:", resTag.status, txt);
        throw new Error("Failed to create tag");
      }

      let createdTag: Tag = await resTag.json();

      // Some backends return wrapper { tag: {...} } — normalize
      if ((createdTag as any).tag) createdTag = (createdTag as any).tag;

      // link tag to file
      const resAssoc = await fetch(`/api/file-tags`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Authorization: `Bearer ${token}`,
        },
        body: JSON.stringify({
          id_file: file.id,
          id_tag: createdTag.id,
        }),
      });

      if (!resAssoc.ok) {
        const txt = await resAssoc.text();
        console.error("Assoc create failed:", resAssoc.status, txt);
        throw new Error("Failed to link tag to file");
      }

      const assocObj: FileTag = await resAssoc.json();

      // Update UI: add created tag & assoc
      setTags((prev) => [...prev, createdTag]);
      setAssociations((prev) => [...prev, assocObj]);

      // reset newTag
      setNewTag({ name: "", description: "", color: "#ffffff" });
      setError(null);
    } catch (err) {
      console.error("addTag error:", err);
      setError("Failed to create/link tag");
    } finally {
      setLoading(false);
    }
  };

  if (!file) return <p className="p-6 text-white">Loading file...</p>;

  const mime = mimeForType(file);

  return (
    <div className="flex flex-col items-center min-h-screen p-6 bg-[#1E2022] text-white">
      <h1 className="text-3xl font-bold mb-6">Edit File</h1>
      {error && <p className="text-red-500 mb-4">{error}</p>}

      {/* Preview */}
      <div className="mb-4">
        {fileContent ? (
          file.type === "IMAGE" ? (
            <>
              <img
                src={`data:${mime};base64,${fileContent}`}
                alt={file.name}
                className={`rounded cursor-pointer ${fullscreen ? "max-h-screen" : "max-h-60"}`}
                onClick={() => setFullscreen(true)}
              />
              {fullscreen && (
                <div
                  className="fixed inset-0 bg-black bg-opacity-90 flex items-center justify-center z-50"
                  onClick={() => setFullscreen(false)}
                >
                  <img src={`data:${mime};base64,${fileContent}`} alt={file.name} className="max-h-screen max-w-screen" />
                </div>
              )}
            </>
          ) : file.type === "VIDEO" ? (
            <video controls className="max-h-60 rounded">
              <source src={`data:${mime};base64,${fileContent}`} type={mime} />
              Your browser does not support the video tag.
            </video>
          ) : (
            <a
              href={`data:${mime};base64,${fileContent}`}
              download={file.name}
              className="underline"
            >
              Download {file.name}
            </a>
          )
        ) : (
          <p>No preview available</p>
        )}
      </div>

      {/* File name */}
      <div className="mb-6 w-full max-w-md">
        <label className="block mb-2">File Name</label>
        <input
          type="text"
          value={name}
          onChange={(e) => setName(e.target.value)}
          className="w-full p-2 rounded bg-gray-700 text-white"
        />
      </div>

      {/* Tags */}
      <div className="w-full max-w-2xl mb-6">
        <h2 className="text-xl font-semibold mb-2">Tags</h2>

        {tags.map((tag, idx) => (
          <div key={tag.id} className="flex items-center gap-2 p-2 mb-2 rounded bg-gray-800">
            <div className="w-6 h-6 rounded-full" style={{ backgroundColor: tag.color }} />
            <p className="flex-1">{tag.name} – {tag.description}</p>
            <button onClick={() => unlinkTag(associations[idx]?.id)} className="text-red-400 hover:text-red-600">
              ✕
            </button>
          </div>
        ))}

        <div className="flex gap-2 items-center p-2 rounded bg-gray-800 mt-2">
          <input
            type="text"
            placeholder="Tag name"
            value={newTag.name}
            onChange={(e) => setNewTag({ ...newTag, name: e.target.value })}
            className="p-1 rounded bg-gray-700"
          />
          <input
            type="text"
            placeholder="Description"
            value={newTag.description}
            onChange={(e) => setNewTag({ ...newTag, description: e.target.value })}
            className="p-1 rounded bg-gray-700"
          />
          <input
            type="color"
            value={newTag.color}
            onChange={(e) => setNewTag({ ...newTag, color: e.target.value })}
            className="w-12 h-8 p-0 rounded border border-gray-500"
          />
          <button onClick={addTag} className="px-3 py-1 bg-blue-600 rounded hover:bg-blue-700">
            + Add
          </button>
        </div>
      </div>

      {/* Actions */}
      <div className="flex gap-4">
        <button onClick={handleSave} disabled={loading} className="px-6 py-2 bg-green-600 rounded shadow hover:bg-green-700">
          {loading ? "Saving..." : "Save"}
        </button>
        <button onClick={() => router.push("/files")} className="px-6 py-2 bg-gray-600 rounded shadow hover:bg-gray-700">
          Cancel
        </button>
        <button onClick={handleDelete} className="px-6 py-2 bg-red-600 rounded shadow hover:bg-red-700">
          Delete File
        </button>
      </div>
    </div>
  );
}
