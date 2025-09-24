export default function SideBar() {
  return (
    <div 
      className="w-60 p-4 flex flex-col h-full"
      style={{ backgroundColor: '#181A1B' }}
    >
      {/* Search tags */}
      <input
        type="text"
        placeholder="Search tags..."
        className="border px-2 py-1 rounded mb-2"
      />

      {/* Buttons */}
      <button className="mb-2 px-3 py-1 rounded" style={{ backgroundColor: '#25282A' }}>All Tags</button>
      <button className="mb-4 px-3 py-1 text-white rounded" style={{ backgroundColor: '#00A140' }}>Add Tag</button>

      {/* Tags list */}
      <div className="flex-1 overflow-auto space-y-2">
        <div className="px-2 py-1 rounded" style={{ backgroundColor: '#1E2022' }}>Tag 1</div>
        <div className="px-2 py-1 rounded" style={{ backgroundColor: '#1E2022' }}>Tag 2</div>
        <div className="px-2 py-1  rounded" style={{ backgroundColor: '#1E2022' }}>Tag 3</div>
      </div>
    </div>
  );
}