"use client";
import { useRouter } from "next/navigation";

interface TopBarProps {
  columns: number;
  setColumns: (cols: number) => void;
}

export default function TopBar({ columns, setColumns }: TopBarProps) {

  const router = useRouter();

  const handleLogout = () => {
    const confirmed = window.confirm("Are you sure you want to logout?");
    if (confirmed) {
      // Add your logout logic here if needed (e.g., clear tokens)

      // Redirect to home page
      router.push("/");
    }
  };

  return (
    <div 
      className="flex items-center justify-between p-2 shadow-sm"
      style={{ backgroundColor: '#181A1B' }}
    >
      {/* Left side */}
      <div className="flex items-center gap-2">
        <button className="px-3 py-1 text-white rounded" style={{ backgroundColor: '#0047B2' }}>Add File</button>
        <input
          type="text"
          placeholder="Search files..."
          className="border px-2 py-1 rounded"
        />
      </div>

      {/* Filters */}
      <div className="flex items-center gap-2">
        <button className="px-3 py-1 rounded" style={{ backgroundColor: '#25282A' }}>Images</button>
        <button className="px-3 py-1 rounded" style={{ backgroundColor: '#25282A' }}>Videos</button>
        <button className="px-3 py-1 rounded" style={{ backgroundColor: '#25282A' }}>Docs</button>
        <button className="px-3 py-1 rounded" style={{ backgroundColor: '#25282A' }}>Audios</button>
        <button className="px-3 py-1 rounded" style={{ backgroundColor: '#25282A' }}>Others</button>

        {/* Dropdown for columns */}
        <select
          value={columns}
          onChange={(e) => setColumns(Number(e.target.value))}
          className="border px-2 py-1 rounded"
          style={{ backgroundColor: '#25282A'}}
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
        style={{ backgroundColor: '#B1030C' }}
      >
        Logout
      </button>
    </div>
  );
}