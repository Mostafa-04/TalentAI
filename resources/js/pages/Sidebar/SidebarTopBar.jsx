import { useState,useEffect } from "react";
import { Search, ShieldCheck, Moon, Sun } from "lucide-react";

export default function SidebarTopBar() {
  const [lang, setLang] = useState("fr");
    const [dark, setDark] = useState(false);
    const [collapsed, setCollapsed] = useState(false);
  
    const isActive = (href) => url.startsWith(href);
  
    // DARK MODE INIT
    useEffect(() => {
      const saved = localStorage.getItem("theme");
  
      if (saved === "dark") {
        document.documentElement.classList.add("dark");
        setDark(true);
      }
    }, []);
  
    const toggleDark = () => {
      if (dark) {
        document.documentElement.classList.remove("dark");
        localStorage.setItem("theme", "light");
      } else {
        document.documentElement.classList.add("dark");
        localStorage.setItem("theme", "dark");
      }
      setDark(!dark);
    };

  return (
    <div className="px-20 py-3 border-b border-gray-200 dark:border-white/10">

    {/* ONE LINE DISTRIBUTED */}
    <div className="flex items-center justify-between gap-3">

        {/* LEFT - SEARCH */}
        {!collapsed && (
        <div className="flex items-center gap-2 flex-1 max-w-[400px] bg-gray-100 dark:bg-[#1E1E28] px-3 py-2 rounded-lg">
            <Search size={16} className="text-gray-400" />
            <input
            type="text"
            placeholder="Search..."
            className="bg-transparent outline-none text-sm w-full text-gray-700 dark:text-white"
            />
        </div>
        )}

        {/* CENTER - SSL + LANGUAGE */}
        <div className="flex items-center gap-4">

        {/* SSL */}
        <div className="flex items-center gap-1 text-green-500">
            <ShieldCheck size={18} />
            {!collapsed && (
            <span className="text-xs font-medium">SSL</span>
            )}
        </div>

        {/* LANGUAGE */}
        {!collapsed && (
            <select
            value={lang}
            onChange={(e) => setLang(e.target.value)}
            className="text-xs bg-transparent border border-gray-300 dark:border-white/10 rounded px-2 py-1 text-gray-700 dark:text-white"
            >
            <option value="fr">FR</option>
            <option value="en">EN</option>
            </select>
        )}

        </div>

        {/* RIGHT - DARK MODE */}
        <button
        onClick={toggleDark}
        className="p-2 rounded-lg bg-gray-100 dark:bg-[#1E1E28] hover:scale-105 transition"
        >
        {dark ? <Sun size={16} /> : <Moon size={16} />}
        </button>

    </div>
    </div>
  );
}