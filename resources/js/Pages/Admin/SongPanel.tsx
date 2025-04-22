
import {useState} from "react";
import axios from "axios";

export interface Song {
    id: number;
    spotify_id: string;
    title: string;
    artist: string;
    uri: string;
    album: string;
    album_image: string;
    release_year: string;
}

interface Props {
    songs: Song[];
}

export default function SongPanel({ songs: initialSongs }: Props) {

    const [songs, setSongs] = useState<Song[]>(initialSongs);

    const handleDelete = async (id: number) => {
        try {
            await axios.delete(`/api/songs/${id}`);
            setSongs((prev) => prev.filter((song) => song.id !== id));
        } catch (error) {
            console.error("Failed to delete song", error);
        }
    };

    return (
        <>
            <div className="dark min-h-screen bg-gray-900 text-white">
                <div className="min-h-screen bg-gray-100 dark:bg-gray-900 p-6">
                    <div className="max-w-4xl mx-auto bg-white dark:bg-gray-800 shadow-xl rounded-2xl p-6">
                        <h1 className="text-2xl font-bold mb-4 text-gray-800 dark:text-white">🎵 Song Queue Admin
                            Panel</h1>
                        {songs.length === 0 ? (
                            <p className="text-gray-600 dark:text-gray-400">No songs have been added yet.</p>
                        ) : (
                            <table className="w-full text-left border-t border-gray-200 dark:border-gray-700">
                                <thead>
                                <tr className="text-sm font-semibold text-gray-700 dark:text-gray-300">
                                    <th className="py-2">Song Info</th>
                                </tr>
                                </thead>
                                <tbody>
                                {songs.map((song) => (
                                    <tr key={song.id}
                                        className="border-t border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td className="py-2">
                                            <div className="flex items-center gap-4">
                                                <img
                                                    src={song.album_image}
                                                    alt={song.album}
                                                    className="w-12 h-12 rounded shadow-sm"
                                                />
                                                <div>
                                                    <div
                                                        className="font-semibold text-gray-800 dark:text-white">{song.title}</div>
                                                    <div
                                                        className="text-sm text-gray-600 dark:text-gray-400">{song.artist}</div>
                                                    <div
                                                        className="text-xs text-gray-500 dark:text-gray-500">{song.album} ({song.release_year})
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td className="py-2 text-right">
                                            <button
                                                onClick={() => handleDelete(song.id)}
                                                className="text-sm px-3 py-1 bg-red-600 hover:bg-red-700 text-white rounded-lg transition"
                                            >
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                ))}
                                </tbody>
                            </table>
                        )}
                    </div>
                </div>
            </div>
            </>
            );
            }
