import { Head } from "@inertiajs/react";

export interface Song {
    id: number;
    spotify_id: string;
    title: string;
    artist: string;
    uri: string;
}

interface Props {
    songs: Song[];
}

export default function SongPanel({ songs }: Props) {
    return (
        <>
            <Head title="Admin – Songs" />
            <div className="min-h-screen bg-gray-100 p-6">
                <div className="max-w-4xl mx-auto bg-white shadow-xl rounded-2xl p-6">
                    <h1 className="text-2xl font-bold mb-4 text-gray-800">Song Queue Admin Panel</h1>
                    {songs.length === 0 ? (
                        <p className="text-gray-600">No songs have been added yet.</p>
                    ) : (
                        <table className="w-full text-left border-t border-gray-200">
                            <thead>
                            <tr className="text-sm font-semibold text-gray-700">
                                <th className="py-2">Title</th>
                                <th className="py-2">Artist</th>
                                <th className="py-2">Spotify URI</th>
                            </tr>
                            </thead>
                            <tbody>
                            {songs.map((song) => (
                                <tr key={song.id} className="border-t border-gray-200 hover:bg-gray-50">
                                    <td className="py-2">{song.title}</td>
                                    <td className="py-2">{song.artist}</td>
                                    <td className="py-2 text-blue-600 truncate max-w-xs">{song.uri}</td>
                                </tr>
                            ))}
                            </tbody>
                        </table>
                    )}
                </div>
            </div>
        </>
    );
}
