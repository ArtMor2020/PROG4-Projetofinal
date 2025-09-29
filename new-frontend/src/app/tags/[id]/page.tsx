"use client";

import { useEffect, useState } from "react";
import { useParams, useRouter } from "next/navigation";

interface FileModel {
  id: string;
  id_owner: string;
  name: string;
  type: string;
  path: string;
  is_deleted: string;
  content: string;
}

export default function TagFilesPage() {
  const { id: tagId } = useParams();
  const router = useRouter();
  const token = typeof window !== "undefined" ? localStorage.getItem("token") : null;

  const [files, setFiles] = useState<FileModel[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [columns, setColumns] = useState(4);

  useEffect(() => {
    if (!token) {
      router.push("/");
      return;
    }
    if (!tagId) {
      router.push("/tags");
      return;
    }

    const fetchFiles = async () => {
      try {
        const resAssoc = await fetch(`/api/file-tags/tag/${tagId}`, {
          headers: { Authorization: `Bearer ${token}` },
        });
        if (!resAssoc.ok) throw new Error("Failed to fetch file-tag associations");
        const associations: { id: string; id_file: string; id_tag: string }[] = await resAssoc.json();
        const fileIds = associations.map((a) => a.id_file);

        const filePromises = fileIds.map(async (fid) => {
          const resFile = await fetch(`/api/files/${fid}`, {
            headers: { Authorization: `Bearer ${token}` },
          });
          if (!resFile.ok) throw new Error("Failed to fetch file " + fid);
          const fileData: FileModel = await resFile.json();

          const resContent = await fetch(`/api/file/${fid}`, {
            headers: { Authorization: `Bearer ${token}` },
          });
          const contentJson = resContent.ok ? await resContent.json() : { content: null };
          fileData.content = contentJson?.content ?? null;
          return fileData;
        });

        const filesData = await Promise.all(filePromises);
        setFiles(filesData);
      } catch (err: any) {
        console.error(err);
        setError("Failed to load files for this tag");
      } finally {
        setLoading(false);
      }
    };

    fetchFiles();
  }, [tagId, token, router]);

  const mimeForType = (f: FileModel | null) => {
    if (!f) return "application/octet-stream";
    switch ((f.type || "").toUpperCase()) {
      case "IMAGE": return "image/jpeg";
      case "VIDEO": return "video/mp4";
      case "DOCUMENT": return "application/pdf";
      default: return "application/octet-stream";
    }
  };

  const handleLogout = () => {
    localStorage.removeItem("token");
    router.push("/");
  };

  if (loading) return <div className="flex items-center justify-center min-h-screen text-white" style={{ backgroundColor: "#1E2022" }}>Carregando arquivos...</div>;
  if (error) return <div className="p-6 text-red-500">{error}</div>;

  return (
    <div className="flex flex-col items-center min-h-screen bg-[#1E2022] text-white">
      {/* Top bar */}
      <div className="w-full flex flex-col md:flex-row items-center justify-between p-4 mb-6 bg-gray-800 shadow-md rounded-b-lg">
        <h1 className="text-3xl font-bold text-white mb-2 md:mb-0">TagScribe!</h1>
        <div className="flex flex-wrap items-center gap-4">
          <button
            onClick={() => router.push("/files")}
            className="px-4 py-2 text-white rounded-lg shadow hover:bg-blue-700 transition"
            style={{ backgroundColor: "#023DBF" }}
          >
            Arquivos
          </button>
          <button
            onClick={() => router.push("/tags")}
            className="px-4 py-2 text-white rounded-lg shadow hover:bg-green-700 transition"
            style={{ backgroundColor: "#008532" }}
          >
            Tags
          </button>
          <button
            onClick={() => router.push("/files/upload")}
            className="px-4 py-2 text-white rounded-lg shadow hover:bg-purple-700 transition"
            style={{ backgroundColor: "#6A0DAD" }}
          >
            Adicionar Arquivo
          </button>
          <div className="flex items-center gap-2">
            <label htmlFor="columns" className="text-white font-medium">Colunas:</label>
            <select
              id="columns"
              value={columns}
              onChange={(e) => setColumns(Number(e.target.value))}
              className="bg-gray-700 text-white rounded px-2 py-1"
            >
              {[2,3,4,5,6,7,8].map((num) => <option key={num} value={num}>{num}</option>)}
            </select>
          </div>
          <button
            onClick={handleLogout}
            className="px-4 py-2 text-white rounded-lg shadow hover:bg-red-600 transition"
            style={{ backgroundColor: "#BF0000" }}
          >
            Sair
          </button>
        </div>
      </div>

      {/* Files grid */}
      <div className={`w-full max-w-8xl p-4 grid gap-6 overflow-auto`} style={{ gridTemplateColumns: `repeat(${columns}, minmax(0, 1fr))` }}>
        {files.map((file) => (
          <div
            key={file.id}
            className="bg-gray-700 p-2 rounded-lg shadow hover:scale-105 transform transition cursor-pointer"
            onClick={() => router.push(`/files/${file.id}`)}
          >
            {file.type === "IMAGE" && file.content ? (
              <img src={`data:${mimeForType(file)};base64,${file.content}`} alt={file.name} className="w-full aspect-4/3 object-cover rounded" />
            ) : file.type === "VIDEO" && file.content ? (
              <video controls className="w-full aspect-4/3 rounded">
                <source src={`data:${mimeForType(file)};base64,${file.content}`} type={mimeForType(file)} />
              </video>
            ) : (
              <p className="text-center font-semibold">{file.name}</p>
            )}
            <p className="mt-2 text-center text-white">{file.name}</p>
          </div>
        ))}
      </div>
    </div>
  );
}
